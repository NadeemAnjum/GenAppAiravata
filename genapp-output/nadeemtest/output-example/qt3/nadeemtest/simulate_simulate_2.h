void nadeemtest::module_load_simulate_simulate_2()
{
   delete_widgets_layouts( panel1_sub_widgets, panel1_sub_layouts );
   panel1_widget_map.clear();
   panel1_inputs.clear();
   panel1_outputs.clear();
   panel1_map_input.clear();
   panel1_is_input.clear();

   current_module_id = "simulate_2";

   {
      QLabel * lbl = new QLabel( "", this );
      lbl->setMaximumHeight( 12 );
      lbl->show();
      gl_panel1->addWidget( lbl, 1, 0 );
      panel1_sub_widgets.push_back( lbl );

   }

   QGridLayout * gl = new QGridLayout( 0, 1, 1, 3, 3 );
   {
      QLabel * lbl = new QLabel( "", this );
      lbl->setMaximumHeight( 12 );
      lbl->show();
      gl->addWidget( lbl, 0, 2 );
      panel1_sub_widgets.push_back( lbl );
   }

   int ppos = 0; // the base position in the gl_sub_panel

   {
      QLabel * lbl = new QLabel( "Input file  ", this );
      lbl->setMaximumHeight( 22 );
      lbl->show();
      gl->addWidget( lbl, ppos, 0 );
      panel1_sub_widgets.push_back( lbl );

      mQPushButton * pb = new mQPushButton( this );
      pb->setText( "Browse..." );
      pb->setMaximumHeight( 22 );
      pb->show();
      gl->addWidget( pb, ppos, 1 );
      panel1_sub_widgets.push_back( pb );
      connect( pb, SIGNAL( clicked() ), this, SLOT( browse_simulate_2_input1() ) );

      QLabel * lbl2 = new QLabel( "", this );
      pb->mbuddy = lbl2;
      lbl2->setMaximumHeight( 22 );
      lbl2->show();
      gl->addWidget( lbl2, ppos, 2 );
      panel1_sub_widgets.push_back( lbl2 );

      QString id = "simulate_2:input1";
      global_data_types[ id ] = "file";
      panel1_widget_map[ id ] = pb;
      panel1_inputs.push_back( id );
      save_default_value( id );

      ppos++;
   }
   QHBoxLayout * hbl = new QHBoxLayout( 0 );
   {
      mQPushButton * pb = new mQPushButton( this );
      pb->setText( "Submit" );
      pb->setMaximumHeight( 22 );
      pb->show();
      connect( pb, SIGNAL( clicked() ), SLOT( module_submit_simulate_simulate_2() ) );
      hbl->addWidget( pb );
      hbl->addSpacing( 3 );
      panel1_sub_widgets.push_back( pb );
   }
   {
      mQPushButton * pb = new mQPushButton( this );
      pb->setText( "Reset to default values" );
      pb->setMaximumHeight( 22 );
      pb->show();
      connect( pb, SIGNAL( clicked() ), SLOT(module_reset_simulate_simulate_2() ) );
      hbl->addWidget( pb );
      panel1_sub_widgets.push_back( pb );
   }
   gl->addMultiCellLayout( hbl, ppos, ppos, 0, 1 );
   panel1_sub_layouts.push_back( hbl );
   ppos++;

   {
      QLabel * lbl = new QLabel( "", this );
      lbl->setPalette( *palette_lbl_error );
      lbl->show();
      gl->addMultiCellWidget( lbl, ppos, ppos, 0, 1 );
      panel1_sub_widgets.push_back( lbl );

      QString id = "simulate_2:_errorMessages";
      global_data_types[ id ] = "lbl";
      panel1_widget_map[ id ] = lbl;
      panel1_inputs.push_back( id );
      save_default_value( id );
   }
   ppos++;
   {
      QLabel * lbl = new QLabel( "Output value 1  ", this );
      lbl->setMaximumHeight( 22 );
      lbl->show();
      gl->addWidget( lbl, ppos, 0 );
      panel1_sub_widgets.push_back( lbl );

      QLineEdit * le = new QLineEdit( this );
      le->setMaximumHeight( 22 );
      le->setPalette( *palette_le );
      le->setReadOnly( true );
      le->show();
      gl->addWidget( le, ppos, 1 );
      panel1_sub_widgets.push_back( le );

      QString id = "simulate_2:output1";
      global_data_types[ id ] = "le";
      panel1_widget_map[ id ] = le;
      panel1_outputs.push_back( id );
      save_default_value( id );

      ppos++;
   }
   {
      QLabel * lbl = new QLabel( "output file  ", this );
      lbl->setMaximumHeight( 22 );
      lbl->show();
      gl->addWidget( lbl, ppos, 0 );
      panel1_sub_widgets.push_back( lbl );

      mQLabel * lbl2 = new mQLabel( "", this );
      
      lbl2->setMaximumHeight( 22 );
      lbl2->show();
      gl->addWidget( lbl2, ppos, 1 );
      panel1_sub_widgets.push_back( lbl2 );
      connect( lbl2, SIGNAL( pressed() ), this, SLOT( browse_simulate_2_output2() ) );

      QString id = "simulate_2:output2";
      global_data_types[ id ] = "outfile";
      panel1_widget_map[ id ] = lbl2;
      panel1_outputs.push_back( id );
      save_default_value( id );

      ppos++;
   }
   {
      QLabel * lbl = new QLabel( "output file 2  ", this );
      lbl->setMaximumHeight( 22 );
      lbl->show();
      gl->addWidget( lbl, ppos, 0 );
      panel1_sub_widgets.push_back( lbl );

      mQLabel * lbl2 = new mQLabel( "", this );
      
      lbl2->setMaximumHeight( 22 );
      lbl2->show();
      gl->addWidget( lbl2, ppos, 1 );
      panel1_sub_widgets.push_back( lbl2 );
      connect( lbl2, SIGNAL( pressed() ), this, SLOT( browse_simulate_2_output3() ) );

      QString id = "simulate_2:output3";
      global_data_types[ id ] = "outfile";
      panel1_widget_map[ id ] = lbl2;
      panel1_outputs.push_back( id );
      save_default_value( id );

      ppos++;
   }

   reset_last_values();

   gl->setColStretch( 0, 0 );
   gl->setColStretch( 1, 0 );
   gl->setColStretch( 2, 1 );
   gl_panel1->addMultiCellLayout( gl, 2, 2, 0, 2 );
   panel1_sub_layouts.push_back( gl );
   for ( int i = 0; i < (int) panel1_inputs.size(); ++i )
   {
      panel1_is_input[ panel1_inputs[ i ] ] = true;
   }
}

void nadeemtest::module_reset_simulate_simulate_2()
{
   reset_default_values();
}

void nadeemtest::module_submit_simulate_simulate_2()
{
   save_last_values();
   reset_output_values( "default_value" );
   // qDebug( input_to_json( "simulate_2" ) );
   // we should check if process already running

   QString program = "/home/nadeem/nadeemtest/bin/simulate_2";

   QFileInfo qfi( program );
   if ( !qfi.exists() || !qfi.isExecutable() || qfi.isDir() )
   {
      QString id = "simulate_2";
      QString key = id + ":_errorMessages";    
      global_data[ key + ":last_value" ] = QVariant( "Unexpected results:\n   error => command not found or not executable" );
      if ( current_module_id == id &&
           global_data_types.count( key ) )
      {
         reset_value( key, "last_value" );
      }
      return;
   }
   //   qDebug( "process exists and isExecutable" );
   
   // make a temporary directory:

   QString dir = UTILITY::unique_directory( "/tmp/nadeemtest" );
   if ( dir.isEmpty() )
   {  
      QString id = "simulate_2";
      QString key = id + ":_errorMessages";    
      global_data[ key + ":last_value" ] = QVariant( "Unexpected results:\n   error => could not create temporary directory for submission" );
      if ( current_module_id == id &&
           global_data_types.count( key ) )
      {
         reset_value( key, "last_value" );
      }
   }        
   /*
   process_simulate_simulate_2 = new QProcess( this );
   process_simulate_simulate_2->setWorkingDirectory( dir );
   process_simulate_simulate_2->addArgument( program );

   process_simulate_simulate_2->addArgument( input_to_json( "simulate_2", dir ) );
   connect( process_simulate_simulate_2, 
            SIGNAL( readyReadStdout() ), 
            this, 
            SLOT( readyReadStdout_simulate_simulate_2() ) );
   connect( process_simulate_simulate_2, 
            SIGNAL( readyReadStderr() ), 
            this, 
            SLOT( readyReadStderr_simulate_simulate_2() ) );
   connect( process_simulate_simulate_2, 
            SIGNAL( launchFinished() ), 
            this, 
            SLOT( launchFinished_simulate_simulate_2() ) );
   connect( process_simulate_simulate_2, 
            SIGNAL( processExited() ), 
            this, 
            SLOT( processExited_simulate_simulate_2() ) );
   if ( !process_simulate_simulate_2->start() )
   { 
      qDebug( "error starting process" );
      return;
   }
   process_json[ "simulate_2" ] = "";
   qDebug( "process started" );
   */
   process_json[ "simulate_2" ] = "";
   string tprojId = createProject("nadeem", "simulate_2");
   char *projId = new char[tprojId.length() + 1];
   strcpy(projId, tprojId.c_str());
   QString tmp = input_to_json( "simulate_2" );
   std::string tjson(tmp.ascii());
   char *json = new char[tjson.length() + 1];
   strcpy(json, tjson.c_str());
   string texpId = createExperiment("nadeem", "exp1", projId, "simulate_2", json);	
   char *expId = new char[texpId.length() + 1];
   strcpy(expId, texpId.c_str());
   launchExperiment(expId);
   while((getExperimentStatus(expId))!=7){	
	unsigned int microseconds = 1000;
	usleep(microseconds);
   }
   unsigned int microseconds = 500000;
   usleep(microseconds);
   string output = getExperimentOutput(expId);     
   QString qoutput = QString(output);
   process_json[ "simulate_2" ] = qoutput;
   process_results( "simulate_2" );
}

void nadeemtest::readyReadStdout_simulate_simulate_2()
{
   while ( process_simulate_simulate_2->canReadLineStdout() )
   {
      process_json[ "simulate_2" ] += process_simulate_simulate_2->readLineStdout();
   }
}

void nadeemtest::readyReadStderr_simulate_simulate_2()
{
   while ( process_simulate_simulate_2->canReadLineStderr() )
   {
      qDebug( process_simulate_simulate_2->readLineStderr() );
   }
}

void nadeemtest::launchFinished_simulate_simulate_2()
{
   // qDebug( "process_simulate_simulate_2->launchFinished()" );
   disconnect( process_simulate_simulate_2,
               SIGNAL( launchFinished_simulate_simulate_2() ),
               0, 0 );
}

void nadeemtest::processExited_simulate_simulate_2()
{
   qDebug( "process_simulate_simulate_2->processExited()" );
   readyReadStdout_simulate_simulate_2();
   readyReadStderr_simulate_simulate_2();

   disconnect( process_simulate_simulate_2, 0, 0, 0 );
   // post process data into output fields, add unexpected data etc
   // delete process_simulate_simulate_2;
   // process_simulate_simulate_2 = (QProcess *) 0;
   process_results( "simulate_2" );
}
