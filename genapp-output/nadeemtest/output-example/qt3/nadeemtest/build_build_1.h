void nadeemtest::module_load_build_build_1()
{
   delete_widgets_layouts( panel1_sub_widgets, panel1_sub_layouts );
   panel1_widget_map.clear();
   panel1_inputs.clear();
   panel1_outputs.clear();
   panel1_map_input.clear();
   panel1_is_input.clear();

   current_module_id = "build_1";

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
      QLabel * lbl = new QLabel( "Input value 1  ", this );
      lbl->setMaximumHeight( 22 );
      lbl->show();
      gl->addWidget( lbl, ppos, 0 );
      panel1_sub_widgets.push_back( lbl );

      QLineEdit * le = new QLineEdit( this );
      le->setMaximumHeight( 22 );
      le->setPalette( *palette_le );
      le->setValidator( new QDoubleValidator( le ) );
      ((QDoubleValidator*)le->validator())->setDecimals( 8 );
      le->setText( QString( "%1" ).arg( 0 ) ); 
      ((QDoubleValidator*)le->validator())->setBottom( 0 ); 
      ((QDoubleValidator*)le->validator())->setTop( 1e+50 ); 
      le->show();
      gl->addWidget( le, ppos, 1 );
      panel1_sub_widgets.push_back( le );

      QString id = "build_1:input1";
      global_data_types[ id ] = "le";
      panel1_widget_map[ id ] = le;
      panel1_inputs.push_back( id );
      save_default_value( id );

      ppos++;
   }
   {
      QLabel * lbl = new QLabel( "Input value 2  ", this );
      lbl->setMaximumHeight( 22 );
      lbl->show();
      gl->addWidget( lbl, ppos, 0 );
      panel1_sub_widgets.push_back( lbl );

      QLineEdit * le = new QLineEdit( this );
      le->setMaximumHeight( 22 );
      le->setPalette( *palette_le );
      le->setValidator( new QIntValidator( le ) );
      le->setText( QString( "%1" ).arg( 1 ) ); 
      ((QIntValidator*)le->validator())->setBottom( -10 ); 
      ((QIntValidator*)le->validator())->setTop( 10 ); 
      le->show();
      gl->addWidget( le, ppos, 1 );
      panel1_sub_widgets.push_back( le );

      QString id = "build_1:input2";
      global_data_types[ id ] = "le";
      panel1_widget_map[ id ] = le;
      panel1_inputs.push_back( id );
      save_default_value( id );

      ppos++;
   }
   {
      mQLabel * lbl = new mQLabel( "Input value 3  ", this );
      lbl->setMaximumHeight( 22 );
      lbl->show();
      gl->addWidget( lbl, ppos, 0 );
      panel1_sub_widgets.push_back( lbl );

      QCheckBox * cb = new QCheckBox( this );
      cb->setMaximumHeight( 22 );
      cb->setPalette( *palette_cb );
      cb->setChecked( true ); 
      cb->show();
      gl->addWidget( cb, ppos, 1 );
      panel1_sub_widgets.push_back( cb );
      connect( lbl, SIGNAL( pressed() ), cb, SLOT( toggle() ) );

      QString id = "build_1:input3";
      global_data_types[ id ] = "cb";
      panel1_widget_map[ id ] = cb;
      panel1_inputs.push_back( id );
      save_default_value( id );

      ppos++;
   }
   {
      QLabel * lbl = new QLabel( "Input text 10  ", this );
      lbl->setMaximumHeight( 22 );
      lbl->show();
      gl->addWidget( lbl, ppos, 0 );
      panel1_sub_widgets.push_back( lbl );

      QLineEdit * le = new QLineEdit( this );
      le->setMaximumHeight( 22 );
      le->setPalette( *palette_le );
     
      le->setValidator( new QRegExpValidator( QRegExp( "^.{,10}$" ), this ) ); 
      le->show();
      gl->addWidget( le, ppos, 1 );
      panel1_sub_widgets.push_back( le );

      QString id = "build_1:input4";
      global_data_types[ id ] = "le";
      panel1_widget_map[ id ] = le;
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
      connect( pb, SIGNAL( clicked() ), SLOT( module_submit_build_build_1() ) );
      hbl->addWidget( pb );
      hbl->addSpacing( 3 );
      panel1_sub_widgets.push_back( pb );
   }
   {
      mQPushButton * pb = new mQPushButton( this );
      pb->setText( "Reset to default values" );
      pb->setMaximumHeight( 22 );
      pb->show();
      connect( pb, SIGNAL( clicked() ), SLOT(module_reset_build_build_1() ) );
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

      QString id = "build_1:_errorMessages";
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

      QString id = "build_1:output1";
      global_data_types[ id ] = "le";
      panel1_widget_map[ id ] = le;
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

void nadeemtest::module_reset_build_build_1()
{
   reset_default_values();
}

void nadeemtest::module_submit_build_build_1()
{
   save_last_values();
   reset_output_values( "default_value" );
   // qDebug( input_to_json( "build_1" ) );
   // we should check if process already running

   QString program = "/home/nadeem/nadeemtest/bin/build_1";

   QFileInfo qfi( program );
   if ( !qfi.exists() || !qfi.isExecutable() || qfi.isDir() )
   {
      QString id = "build_1";
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
      QString id = "build_1";
      QString key = id + ":_errorMessages";    
      global_data[ key + ":last_value" ] = QVariant( "Unexpected results:\n   error => could not create temporary directory for submission" );
      if ( current_module_id == id &&
           global_data_types.count( key ) )
      {
         reset_value( key, "last_value" );
      }
   }        
   /*
   process_build_build_1 = new QProcess( this );
   process_build_build_1->setWorkingDirectory( dir );
   process_build_build_1->addArgument( program );

   process_build_build_1->addArgument( input_to_json( "build_1", dir ) );
   connect( process_build_build_1, 
            SIGNAL( readyReadStdout() ), 
            this, 
            SLOT( readyReadStdout_build_build_1() ) );
   connect( process_build_build_1, 
            SIGNAL( readyReadStderr() ), 
            this, 
            SLOT( readyReadStderr_build_build_1() ) );
   connect( process_build_build_1, 
            SIGNAL( launchFinished() ), 
            this, 
            SLOT( launchFinished_build_build_1() ) );
   connect( process_build_build_1, 
            SIGNAL( processExited() ), 
            this, 
            SLOT( processExited_build_build_1() ) );
   if ( !process_build_build_1->start() )
   { 
      qDebug( "error starting process" );
      return;
   }
   process_json[ "build_1" ] = "";
   qDebug( "process started" );
   */
   process_json[ "build_1" ] = "";
   string tprojId = createProject("nadeem", "build_1");
   char *projId = new char[tprojId.length() + 1];
   strcpy(projId, tprojId.c_str());
   QString tmp = input_to_json( "build_1" );
   std::string tjson(tmp.ascii());
   char *json = new char[tjson.length() + 1];
   strcpy(json, tjson.c_str());
   string texpId = createExperiment("nadeem", "exp1", projId, "build_1", json);	
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
   process_json[ "build_1" ] = qoutput;
   process_results( "build_1" );
}

void nadeemtest::readyReadStdout_build_build_1()
{
   while ( process_build_build_1->canReadLineStdout() )
   {
      process_json[ "build_1" ] += process_build_build_1->readLineStdout();
   }
}

void nadeemtest::readyReadStderr_build_build_1()
{
   while ( process_build_build_1->canReadLineStderr() )
   {
      qDebug( process_build_build_1->readLineStderr() );
   }
}

void nadeemtest::launchFinished_build_build_1()
{
   // qDebug( "process_build_build_1->launchFinished()" );
   disconnect( process_build_build_1,
               SIGNAL( launchFinished_build_build_1() ),
               0, 0 );
}

void nadeemtest::processExited_build_build_1()
{
   qDebug( "process_build_build_1->processExited()" );
   readyReadStdout_build_build_1();
   readyReadStderr_build_build_1();

   disconnect( process_build_build_1, 0, 0, 0 );
   // post process data into output fields, add unexpected data etc
   // delete process_build_build_1;
   // process_build_build_1 = (QProcess *) 0;
   process_results( "build_1" );
}
