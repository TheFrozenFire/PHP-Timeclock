<?php
class TimeClock_GUI {
	private $window;
	private $vbox;
	
	private $calendar;
	private $employeelist;
	
	private $datasource;
	
	private $clockInButton;
	private $clockOutButton;
	
	public function __construct($datasource) {
		$this->datasource = $datasource;
	
		$window = $this->window = new GtkWindow();
		$window->resize(800,600);
		$window->set_title('PHP Time Clock');
		$window->set_icon($window->render_icon(Gtk::STOCK_EDIT, Gtk::ICON_SIZE_DIALOG));
		$window->connect_simple('destroy', array($this, 'shutdown'));
		
		$this->vbox = new GtkVBox();
		$window->add($this->vbox);
		
		$this->buildMenu();
		$this->buildWorkspace();
		
		$window->show_all();
		
		$this->refreshWorkspace($this->calendar);
	}
	
	public function shutdown() {
		Gtk::main_quit();
	}
	
	private function buildMenu() {
		$menu = new GtkMenuBar();
		$this->vbox->pack_start($menu, FALSE);
		
		$menuFile = new GtkMenuItem('_File');
		$menu->add($menuFile);
		$menuFileMenu = new GtkMenu();
		$menuFile->set_submenu($menuFileMenu);
		$menuFileQuit = new GtkImageMenuItem(Gtk::STOCK_QUIT);
		$menuFileMenu->add($menuFileQuit);
		
		$menuFileQuit->connect_simple('activate', array($this, 'shutdown'));
		
		$menuHelp = new GtkMenuItem('_Help');
		$menu->add($menuHelp);
		$menuHelpMenu = new GtkMenu();
		$menuHelp->set_submenu($menuHelpMenu);
		$menuHelpAbout = new GtkImageMenuItem(Gtk::STOCK_ABOUT);
		$menuHelpAbout->connect_simple('activate', array($this, 'aboutDialog'));
		$menuHelpMenu->add($menuHelpAbout);
	}
	
	private function buildWorkspace() {
		$workspace = new GtkHBox();
		$this->vbox->pack_start($workspace, TRUE);
		
		$employeelist = $this->employeelist = new GtkListStore(
			GObject::TYPE_LONG, // Schedule-Entry ID
			GObject::TYPE_LONG, // Employee ID
			GObject::TYPE_STRING, // Employee Name
			GObject::TYPE_STRING, // Expected Arrival Time
			GObject::TYPE_STRING, // Expected Departure Time
			GObject::TYPE_STRING, // Clock-In Time
			GObject::TYPE_STRING, // Clock-Out Time
			GObject::TYPE_BOOLEAN // Clocked In?
		);
		
		$employeeview = new GtkTreeView($employeelist);
		$selection = $employeeview->get_selection();
		$selection->connect('changed', array($this, 'employeeSelected'));
		$workspace->pack_start($employeeview, TRUE);
		
		$text_renderer = new GtkCellRendererText();
		$bool_renderer = new GtkCellRendererToggle();
		
		$idColumn = new GtkTreeViewColumn('Entry ID', $text_renderer, 'text', 0);
		$idColumn->set_expand(FALSE);
		$employeeview->append_column($idColumn);
		
		$employeeColumn = new GtkTreeViewColumn('Employee ID', $text_renderer, 'text', 1);
		$employeeColumn->set_expand(FALSE);
		$employeeview->append_column($employeeColumn);
		
		$nameColumn = new GtkTreeViewColumn('Employee Name', $text_renderer, 'text', 2);
		$nameColumn->set_expand(TRUE);
		$employeeview->append_column($nameColumn);
		
		$arrivalColumn = new GtkTreeViewColumn('Arrival Time', $text_renderer, 'text', 3);
		$arrivalColumn->set_expand(FALSE);
		$employeeview->append_column($arrivalColumn);
		
		$departureColumn = new GtkTreeViewColumn('Departure Time', $text_renderer, 'text', 4);
		$departureColumn->set_expand(FALSE);
		$employeeview->append_column($departureColumn);
		
		$clockInColumn = new GtkTreeViewColumn('Clock-In Time', $text_renderer, 'text', 5);
		$clockInColumn->set_expand(FALSE);
		$employeeview->append_column($clockInColumn);
		
		$clockOutColumn = new GtkTreeViewColumn('Clock-Out Time', $text_renderer, 'text', 6);
		$clockOutColumn->set_expand(FALSE);
		$employeeview->append_column($clockOutColumn);
		
		$clockedInColumn = new GtkTreeViewColumn('Clocked In', $bool_renderer, 'active', 7);
		$clockedInColumn->set_expand(FALSE);
		$employeeview->append_column($clockedInColumn);
		
		$sideMenu = new GtkVBox();
		$workspace->pack_start($sideMenu, FALSE);
		
		$calendar = $this->calendar = new GtkCalendar();
		$calendar->connect_simple('day-selected', array($this, 'refreshWorkspace'));
		$sideMenu->pack_start($calendar, FALSE);
		
		$buttonMenu = new GtkVButtonBox();
		$sideMenu->pack_start($buttonMenu, FALSE);
		
		$buttonMenu->add($clockInButton = $this->clockInButton = new GtkButton('Clock In'));
		$clockInButton->set_image(GtkImage::new_from_stock(Gtk::STOCK_YES, Gtk::ICON_SIZE_BUTTON));
		$clockInButton->set_sensitive(FALSE);
		$clockInButton->connect_simple('clicked', array($this, 'clockIn'), $employeeview);
		$buttonMenu->add($clockOutButton = $this->clockOutButton = new GtkButton('Clock Out'));
		$clockOutButton->set_image(GtkImage::new_from_stock(Gtk::STOCK_NO, Gtk::ICON_SIZE_BUTTON));
		$clockOutButton->set_sensitive(FALSE);
		$clockOutButton->connect_simple('clicked', array($this, 'clockOut'), $employeeview);
		$buttonMenu->add($addScheduleButton = new GtkButton('Add Employee'));
		$addScheduleButton->set_image(GtkImage::new_from_stock(Gtk::STOCK_ADD, Gtk::ICON_SIZE_BUTTON));
		$addScheduleButton->connect_simple('clicked', array($this, 'addEmployee'));
		$buttonMenu->add($removeScheduleButton = new GtkButton('Remove Employee'));
		$removeScheduleButton->connect_simple('clicked', array($this, 'removeEmployee'), $employeeview);
		$removeScheduleButton->set_image(GtkImage::new_from_stock(Gtk::STOCK_REMOVE, Gtk::ICON_SIZE_BUTTON));
	}
	
	public function aboutDialog() {
		$aboutDialog = new GtkAboutDialog();
		$aboutDialog->set_program_name('PHP Time Clock');
		$aboutDialog->set_version('0.1');
		$aboutDialog->set_comments("PHP Time Clock is written entirely in PHP, using PHP-GTK for the Graphical User Interface.\nTo see more about PHP-GTK, please go to http://gtk.php.net");
		$aboutDialog->set_copyright("Copyright (C) Justin Martin 2009");
		$aboutDialog->set_logo($aboutDialog->render_icon(Gtk::STOCK_EDIT, Gtk::ICON_SIZE_LARGE_TOOLBAR));
		$aboutDialog->set_website("http://thefrozenfire.com");
		
		$aboutDialog->run();
		$aboutDialog->destroy();
	}
	
	public function refreshWorkspace() {
		$calendar = $this->calendar;
		$date = $calendar->get_date();
		
		$date = mktime(0, 0, 0, $date[1]+1, $date[2], $date[0]);
		
		$this->employeelist->clear();
		
		if(!$results = $this->datasource->getSchedule($date)) return FALSE;
		
		foreach($results as $result) {
			$this->employeelist->append(
				array(
					$result['id'],
					$result['employee'],
					$result['name'],
					date('g:i A', $result['arrival']),
					date('g:i A', $result['departure']),
					is_null($result['clockin'])?NULL:date('g:i A', $result['clockin']),
					is_null($result['clockout'])?NULL:date('g:i A', $result['clockout']),
					$result['clockedin']
				)
			);
		}
		
		$this->clockInButton->set_sensitive(FALSE);
		$this->clockOutButton->set_sensitive(FALSE);
	}
	
	public function employeeSelected($selection) {
		list($model, $iter) = $selection->get_selected();
		
		if(!$iter) return FALSE;
		
		$clockInValue = $model->get_value($iter, 5);
		$clockOutValue = $model->get_value($iter, 6);
		
		$clockInDisplay = empty($clockInValue);
		$clockOutDisplay = (!empty($clockInValue) && empty($clockOutValue));
		
		$this->clockInButton->set_sensitive($clockInDisplay);
		$this->clockOutButton->set_sensitive($clockOutDisplay);
	}
	
	public function clockIn($view) {
		$selection = $view->get_selection();
		list($model, $iter) = $selection->get_selected();
		if(!$iter) return FALSE;
		$this->datasource->clockIn($model->get_value($iter, 0));
		
		$this->refreshWorkspace();
	}
	
	public function clockOut($view) {
		$selection = $view->get_selection();
		list($model, $iter) = $selection->get_selected();
		if(!$iter) return FALSE;
		$this->datasource->clockOut($model->get_value($iter, 0));
		
		$this->refreshWorkspace();
	}
	
	public function addEmployee() {
		$calendar = $this->calendar;
		$dateArray = $calendar->get_date();
		
		$date = mktime(0, 0, 0, $dateArray[1]+1, $dateArray[2], $dateArray[0]);
		
		$employees = $this->datasource->getEmployees();
	
		$dialog = new GtkDialog(
			'Add Employee for '.date('F j, Y', $date),
			NULL,
			Gtk::DIALOG_MODAL,
			array(
				Gtk::STOCK_CANCEL, Gtk::RESPONSE_CANCEL,
				Gtk::STOCK_OK, Gtk::RESPONSE_OK
			)
		);
		
		$dialog->set_icon($dialog->render_icon(Gtk::STOCK_EDIT, Gtk::ICON_SIZE_DIALOG));
		
		$employeeStore = new GtkListStore(
			GObject::TYPE_LONG,
			GObject::TYPE_STRING
		);
		foreach($employees as $employee) $employeeStore->append(array($employee['id'], "{$employee['lastname']}, {$employee['firstname']}"));
		
		$employeeView = new GtkTreeView($employeeStore);
		$renderer = new GtkCellRendererText();
		$idColumn = new GtkTreeViewColumn('ID', $renderer, 'text', 0);
		$employeeView->append_column($idColumn);
		$nameColumn = new GtkTreeViewColumn('Name', $renderer, 'text', 1);
		$employeeView->append_column($nameColumn);
		
		$dialog->vbox->pack_start($employeeView, TRUE);
		
		$arrivalTimeStore = new GtkListStore(GObject::TYPE_LONG, GObject::TYPE_STRING);
		
		for($hour = 0; $hour != 24; $hour++) for($minute = 0; $minute != 60; $minute += 15)
			$arrivalTimeStore->append(array(mktime($hour, $minute, 0, $dateArray[1]+1, $dateArray[2], $dateArray[0]), date('g:i A', mktime($hour, $minute, 0, $dateArray[1]+1, $dateArray[2], $dateArray[0]))));
			
		$departureTimeStore = new GtkListStore(GObject::TYPE_LONG, GObject::TYPE_STRING);
		
		for($hour = 0; $hour != 24; $hour++) for($minute = 0; $minute != 60; $minute += 15)
			$departureTimeStore->append(array(mktime($hour, $minute, 0, $dateArray[1]+1, $dateArray[2], $dateArray[0]), date('g:i A', mktime($hour, $minute, 0, $dateArray[1]+1, $dateArray[2], $dateArray[0]))));
		
		$arrival = new GtkComboBoxEntry();
		$arrival->set_model($arrivalTimeStore);
		$arrival->set_text_column(1);
		$departure = new GtkComboBoxEntry();
		$departure->set_model($departureTimeStore);
		$departure->set_text_column(1);
		
		$timeBox = new GtkHBox();
		$timeBox->pack_start($arrival, TRUE);
		$timeBox->pack_start($departure, TRUE);
		
		$dialog->vbox->pack_start($timeBox);
		
		$dialog->show_all();
		
		while($dialog->run() == Gtk::RESPONSE_OK) {
			list($model, $iter) = $employeeView->get_selection()->get_selected();
			
			if(!$iter) continue;
			
			$selectedEmployee = $model->get_value($iter, 0);
			
			if(!is_numeric($selectedEmployee)) continue;
			
			$arrivalIter = $arrival->get_active_iter();
			$departureIter = $departure->get_active_iter();
			
			if(is_null($arrivalIter) || is_null($departureIter)) continue;
		
			$selectedArrival = $arrivalTimeStore->get_value($arrivalIter, 0);
			$selectedDeparture = $departureTimeStore->get_value($departureIter, 0);
			
			if(is_null($selectedArrival) || is_null($selectedDeparture)) continue;
			
			if(!is_numeric($selectedArrival) && !$selectedArrival = strtotime($arrivalTimeStore->get_Value($arrivalIter, 1), $date)) continue;
			
			if(!is_numeric($selectedDeparture) && (!$selectedDeparture = strtotime($departureTimeStore->get_Value($departureIter, 1), $date))) continue;
			
			$this->datasource->addScheduleEntry($selectedEmployee, $selectedArrival, $selectedDeparture);
			
			$this->refreshWorkspace();
			
			break;
		}
		
		$dialog->destroy();
		return TRUE;
	}
	
	public function removeEmployee($view) {
		$selection = $view->get_selection();
		list($model, $iter) = $selection->get_selected();
		
		if(!$iter) return FALSE;
		
		$id = $model->get_value($iter, 0);
		
		if(!is_numeric($id)) return FALSE;
		
		$result = $this->datasource->removeScheduleEntry($id);
		
		$this->refreshWorkspace();
		
		return $result;
	}
}
?>
