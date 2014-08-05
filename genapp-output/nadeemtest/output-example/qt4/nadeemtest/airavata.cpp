#include <glib.h>
#include <iostream>
#include <stdint.h>
#include <sys/time.h>
#include <fstream>

#define _WIN32_WINNT 0x501

#include <thrift/transport/TTransport.h>
#include <thrift/transport/TBufferTransports.cpp>
#include <thrift/transport/TSocket.cpp>
#include <thrift/protocol/TProtocol.h>
#include <thrift/protocol/TBinaryProtocol.h>
#include <thrift/protocol/TBinaryProtocol.tcc>
#include <thrift/TApplicationException.cpp>
#include <thrift/transport/TTransportException.cpp>
#include <thrift/protocol/TProtocolException.h>
#include "../lib/airavata/Airavata.h"
#include "../lib/airavata/Airavata.cpp"
#include "../lib/airavata/airavataDataModel_types.h"
#include "../lib/airavata/airavataDataModel_types.cpp"
#include "../lib/airavata/airavataErrors_types.h"
#include "../lib/airavata/airavataErrors_types.cpp"
#include "../lib/airavata/experimentModel_types.h"
#include "../lib/airavata/experimentModel_types.cpp"
#include "../lib/airavata/workspaceModel_types.h"
#include "../lib/airavata/workspaceModel_types.cpp"
#include "../lib/airavata/airavataAPI_types.h"
#include "../lib/airavata/airavataAPI_types.cpp"
#include "../lib/airavata/applicationDeploymentModel_types.h"
#include "../lib/airavata/applicationDeploymentModel_types.cpp"
#include "../lib/airavata/applicationInterfaceModel_types.h"
#include "../lib/airavata/applicationInterfaceModel_types.cpp"
#include "../lib/airavata/gatewayResourceProfileModel_types.h"
#include "../lib/airavata/gatewayResourceProfileModel_types.cpp"
#include "../lib/airavata/computeResourceModel_types.h"
#include "../lib/airavata/computeResourceModel_types.cpp"


typedef struct {
        gchar *airavata_server, *app_catalog_server;
        gint airavata_port, app_catalog_port, airavata_timeout;
} Settings;

using namespace std;
using namespace apache::thrift;
using namespace apache::thrift::protocol;
using namespace apache::thrift::transport;
using namespace apache::airavata::api;
using namespace apache::airavata::model::workspace::experiment;

void readConfigFile(char* cfgfile, string& airavata_server, int& airavata_port, int& airavata_timeout) {

	airavata_server="'localhost'";
	airavata_port = 8930;
	airavata_timeout = 5000;				

}


string createProject(char* owner, char* projectName)
{
        qDebug() << "IN";   
        int airavata_port, airavata_timeout;
        string airavata_server;
	char* cfgfile;
	cfgfile = "./airavata-client-properties.ini";
        qDebug() << "OUT1";
        readConfigFile(cfgfile, airavata_server, airavata_port, airavata_timeout);	
	qDebug() << "OUT2";			
	airavata_server.erase(0,1);
	airavata_server.erase(airavata_server.length()-1,1);			
	boost::shared_ptr<TSocket> socket(new TSocket(airavata_server, airavata_port));
	socket->setSendTimeout(airavata_timeout);
	boost::shared_ptr<TTransport> transport(new TBufferedTransport(socket));	
	boost::shared_ptr<TProtocol> protocol(new TBinaryProtocol(transport));
	AiravataClient airavataclient(protocol);
	transport->open();
	apache::airavata::model::workspace::Project project;				
	project.owner=owner;
	project.name=projectName;
	string _return;
	airavataclient.createProject(_return,project);				
	transport->close();
        qDebug() << "OUT"; 
	return _return;
				
}

string createExperiment(char* usrName, char* expName, char* projId, char* execId, char* inp)
{
        
        int airavata_port, airavata_timeout;
        string airavata_server;
	char* cfgfile;
	cfgfile = "./airavata-client-properties.ini";
	readConfigFile(cfgfile, airavata_server, airavata_port, airavata_timeout);				
	airavata_server.erase(0,1);
	airavata_server.erase(airavata_server.length()-1,1);			
	boost::shared_ptr<TSocket> socket(new TSocket(airavata_server, airavata_port));
	socket->setSendTimeout(airavata_timeout);
	boost::shared_ptr<TTransport> transport(new TBufferedTransport(socket));	
	boost::shared_ptr<TProtocol> protocol(new TBinaryProtocol(transport));
	AiravataClient airavataclient(protocol);
	transport->open();

	ComputationalResourceScheduling cmRST;
	cmRST.__set_resourceHostId("localhost");
	cmRST.__set_computationalProjectAccount("nadeem");
	cmRST.__set_totalCPUCount(1);
	cmRST.__set_nodeCount(1);
	cmRST.__set_numberOfThreads(0);
	cmRST.__set_queueName("normal");
	cmRST.__set_wallTimeLimit(15);
	cmRST.__set_jobStartTime(0);
	cmRST.__set_totalPhysicalMemory(0);


	UserConfigurationData userConfigurationData;
	userConfigurationData.__set_airavataAutoSchedule(0);
	userConfigurationData.__set_overrideManualScheduledParams(0);
	userConfigurationData.__set_computationalResourceScheduling(cmRST);


	
	char* appId = execId;        

	 
	DataObjectType input;
	input.__set_key("input");
	input.__set_value(inp);
	input.__set_type(DataType::STRING);
	std::vector<DataObjectType> exInputs;
	exInputs.push_back(input);				
	DataObjectType output;
	output.__set_key("output");
	output.__set_value("");
	output.__set_type(DataType::STDOUT);
	std::vector<DataObjectType> exOutputs;
	exOutputs.push_back(output);


	char* user = usrName;
	char* exp_name = expName;
	char* proj = projId;

	Experiment experiment;
	experiment.__set_projectID(proj);
	experiment.__set_userName(user);
	experiment.__set_name(exp_name);
	experiment.__set_applicationId(appId);
	experiment.__set_userConfigurationData(userConfigurationData);
	experiment.__set_experimentInputs(exInputs);
	experiment.__set_experimentOutputs(exOutputs);
				
	string _return = "";
	airavataclient.createExperiment(_return, experiment);

	transport->close();
	return _return;
}

void launchExperiment(char* expId)
{
        
	int airavata_port, airavata_timeout;
	string airavata_server;
	char* cfgfile;
	cfgfile = "./airavata-client-properties.ini";
	readConfigFile(cfgfile, airavata_server, airavata_port, airavata_timeout);				
	airavata_server.erase(0,1);
	airavata_server.erase(airavata_server.length()-1,1);			
	boost::shared_ptr<TSocket> socket(new TSocket(airavata_server, airavata_port));
	socket->setSendTimeout(airavata_timeout);
	boost::shared_ptr<TTransport> transport(new TBufferedTransport(socket));	
	boost::shared_ptr<TProtocol> protocol(new TBinaryProtocol(transport));
	AiravataClient airavataclient(protocol);
	transport->open();						
	airavataclient.launchExperiment(expId, "airavataToken");
	transport->close();				
}

int getExperimentStatus(char* expId)
{
        
	int airavata_port, airavata_timeout;
	string airavata_server;
	char* cfgfile;
	cfgfile = "./airavata-client-properties.ini";
	readConfigFile(cfgfile, airavata_server, airavata_port, airavata_timeout);				
	airavata_server.erase(0,1);
	airavata_server.erase(airavata_server.length()-1,1);			
	boost::shared_ptr<TSocket> socket(new TSocket(airavata_server, airavata_port));
	socket->setSendTimeout(airavata_timeout);
	boost::shared_ptr<TTransport> transport(new TBufferedTransport(socket));	
	boost::shared_ptr<TProtocol> protocol(new TBinaryProtocol(transport));
	AiravataClient airavataclient(protocol);
	transport->open();			
	ExperimentStatus _return;		
	airavataclient.getExperimentStatus(_return, expId);
	transport->close();
	return _return.experimentState;
				
}

string getExperimentOutput(char* expId)
{
        
	int airavata_port, airavata_timeout;
	string airavata_server;
	char* cfgfile;
	cfgfile = "./airavata-client-properties.ini";
	readConfigFile(cfgfile, airavata_server, airavata_port, airavata_timeout);				
	airavata_server.erase(0,1);
	airavata_server.erase(airavata_server.length()-1,1);			
	boost::shared_ptr<TSocket> socket(new TSocket(airavata_server, airavata_port));
	socket->setSendTimeout(airavata_timeout);
	boost::shared_ptr<TTransport> transport(new TBufferedTransport(socket));	
	boost::shared_ptr<TProtocol> protocol(new TBinaryProtocol(transport));
	AiravataClient airavataclient(protocol);
	transport->open();			
	std::vector<DataObjectType> _return;
	airavataclient.getExperimentOutputs(_return, expId);
	transport->close();
	return _return[0].value;
				
}
