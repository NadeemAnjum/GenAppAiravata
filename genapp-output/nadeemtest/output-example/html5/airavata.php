<?php

$GLOBALS['THRIFT_ROOT'] = 'lib/Thrift/';
require_once $GLOBALS['THRIFT_ROOT'] . 'Transport/TTransport.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'Transport/TBufferedTransport.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'Transport/TSocket.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'Protocol/TProtocol.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'Protocol/TBinaryProtocol.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'Exception/TException.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'Exception/TApplicationException.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'Exception/TProtocolException.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'Exception/TTransportException.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'Base/TBase.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'Type/TType.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'Type/TMessageType.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'Factory/TStringFuncFactory.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'StringFunc/TStringFunc.php';
require_once $GLOBALS['THRIFT_ROOT'] . 'StringFunc/Core.php';

$GLOBALS['AIRAVATA_ROOT'] = 'lib/Airavata/';
require_once $GLOBALS['AIRAVATA_ROOT'] . 'API/Airavata.php';
require_once $GLOBALS['AIRAVATA_ROOT'] . 'Model/Workspace/Types.php';
require_once $GLOBALS['AIRAVATA_ROOT'] . 'Model/Workspace/Experiment/Types.php';
require_once $GLOBALS['AIRAVATA_ROOT'] . 'API/Error/Types.php';

require_once 'lib/AiravataClientFactory.php';

use Airavata\API\Error\AiravataClientException;
use Airavata\API\Error\AiravataSystemException;
use Airavata\API\Error\InvalidRequestException;
use Airavata\Client\AiravataClientFactory;
use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TBufferedTransport;
use Thrift\Transport\TSocket;
use Airavata\API\AiravataClient;
use Airavata\Model\Workspace\Project;
use Airavata\Model\Workspace\Experiment\Experiment;
use Airavata\Model\Workspace\Experiment\DataObjectType;
use Airavata\Model\Workspace\Experiment\UserConfigurationData;
use Airavata\Model\Workspace\Experiment\ComputationalResourceScheduling;
use Airavata\Model\Workspace\Experiment\DataType;
use Airavata\API\Error\ExperimentNotFoundException;	
use Airavata\Model\Workspace\Experiment\ExperimentState;
use Thrift\Exception\TTransportException;

#airavata functions
function createProject($owner, $projectName){	
	$airavataconfig = parse_ini_file("airavata-client-properties.ini");
	$transport = new TSocket($airavataconfig['AIRAVATA_SERVER'], $airavataconfig['AIRAVATA_PORT']);
	$transport->setSendTimeout($airavataconfig['AIRAVATA_TIMEOUT']);
	$protocol = new TBinaryProtocol($transport);
	$transport->open();
	$airavataclient = new AiravataClient($protocol);
	try
	{	    
		$project = new Project();
		$project->owner = $owner;
		$project->name = $projectName;		
		$projId = $airavataclient->createProject($project);
		
		if ($projId)
		{
		    return "$projId";
		}
		else
		{
		    echo 'Failed to create project.';
		}
	    
	}
	catch (InvalidRequestException $ire)
	{
	    print 'InvalidRequestException: ' . $ire->getMessage()."\n";
	}
	catch (AiravataClientException $ace)
	{
	    print 'Airavata System Exception: ' . $ace->getMessage()."\n";
	}
	catch (AiravataSystemException $ase)
	{
	    print 'Airavata System Exception: ' . $ase->getMessage()."\n";
	}
	$transport->close();
}

function createExperiment($usrName, $expName, $projId, $execId, $inp){	
	$airavataconfig = parse_ini_file("airavata-client-properties.ini");
	$transport = new TSocket($airavataconfig['AIRAVATA_SERVER'], $airavataconfig['AIRAVATA_PORT']);
	$transport->setSendTimeout($airavataconfig['AIRAVATA_TIMEOUT']);
	$protocol = new TBinaryProtocol($transport);
	$transport->open();
	$airavataclient = new AiravataClient($protocol);
	try
	{	    
		/* ComputationalResourceScheduling data for Trestles*/
		$cmRST = new ComputationalResourceScheduling();
		$cmRST->resourceHostId = "localhost";
		$cmRST->ComputationalProjectAccount = "ixxi-2013";
		$cmRST->nodeCount = 1;
		$cmRST->numberOfThreads = 0;
		$cmRST->queueName = "normal";
		$cmRST->wallTimeLimit = 15;
		$cmRST->jobStartTime = 0;
		$cmRST->totalPhysicalMemory = 0;

		$cmRS = $cmRST;
		$userConfigurationData = new UserConfigurationData();
		$userConfigurationData->airavataAutoSchedule = 0;
		$userConfigurationData->overrideManualScheduledParams = 0;
		$userConfigurationData->computationalResourceScheduling = $cmRS;

		
		$appId = $execId;

		/* Experiment input and output data. */
		$input = new DataObjectType();
		$input->key = "input";
		$input->value = $inp;		
		$input->type = DataType::STRING;
		$exInputs = array($input);		
		$output = new DataObjectType();
		$output->key = "output";
		$output->value = "";
		$output->type = DataType::STDOUT;
		$exOutputs = array($output);

		/* Create Experiment: needs to update using unique project ID. */
		$user = $usrName;
		$exp_name = $expName;
		$proj = $projId;

		$experiment = new Experiment();
		$experiment->projectID = $proj;
		$experiment->userName = $user;
		$experiment->name = $exp_name;
		$experiment->applicationId = $appId;
		$experiment->userConfigurationData = $userConfigurationData;
		$experiment->experimentInputs = $exInputs;
		$experiment->experimentOutputs = $exOutputs;

		$expId = $airavataclient->createExperiment($experiment);

		if ($expId)
		{
		    var_dump($experiment);
		    return $expId;
		}
		else
		{
		    return 0;
		}
	}
	catch (InvalidRequestException $ire)
	{
		
	    print 'InvalidRequestException: ' . $ire->getMessage()."\n";
	    return -1;
	}
	catch (AiravataClientException $ace)
	{
	    print 'Airavata System Exception: ' . $ace->getMessage()."\n";
	    return -2;
	}
	catch (AiravataSystemException $ase)
	{
	    print 'Airavata System Exception: ' . $ase->getMessage()."\n";
	    return -3;
	}
	$transport->close();
}

function launchExperiment($expId){
	$airavataconfig = parse_ini_file("airavata-client-properties.ini");
	$transport = new TSocket($airavataconfig['AIRAVATA_SERVER'], $airavataconfig['AIRAVATA_PORT']);
	$transport->setSendTimeout($airavataconfig['AIRAVATA_TIMEOUT']);
	$protocol = new TBinaryProtocol($transport);
	$transport->open();
	$airavataclient = new AiravataClient($protocol);	
	try
	{	   
	   $airavataclient->launchExperiment($expId, 'airavataToken');
	   return 1;
	}
	catch (InvalidRequestException $ire)
	{
	    return -1;
	}
	catch (AiravataClientException $ace)
	{
	    return -2;
	}
	catch (AiravataSystemException $ase)
	{
	    return -3;
	}
	catch (ExperimentNotFoundException $enf)
	{
	    return -4;
	}
	$transport->close();
}

function get_experiment_outputs($expId)
{
    $airavataconfig = parse_ini_file("airavata-client-properties.ini");
	$transport = new TSocket($airavataconfig['AIRAVATA_SERVER'], $airavataconfig['AIRAVATA_PORT']);
	$transport->setSendTimeout($airavataconfig['AIRAVATA_TIMEOUT']);
	$protocol = new TBinaryProtocol($transport);
	$transport->open();
	$airavataclient = new AiravataClient($protocol);

    try
    {
        return $airavataclient->getExperimentOutputs($expId);
    }
    catch (InvalidRequestException $ire)
    {
        echo 'InvalidRequestException!<br><br>' . $ire->getMessage();
    }
    catch (ExperimentNotFoundException $enf)
    {
        echo 'ExperimentNotFoundException!<br><br>' . $enf->getMessage();
    }
    catch (AiravataClientException $ace)
    {
        echo 'AiravataClientException!<br><br>' . $ace->getMessage();
    }
    catch (AiravataSystemException $ase)
    {
        echo 'AiravataSystemException!<br><br>' . $ase->getMessage();
    }
    catch (TTransportException $tte)
    {
        echo 'TTransportException!<br><br>' . $tte->getMessage();
    }
    catch (\Exception $e)
    {
        echo 'Exception!<br><br>' . $e->getMessage();
    }

}


function getOutput($expId){	

	$airavataconfig = parse_ini_file("airavata-client-properties.ini");
	$transport = new TSocket($airavataconfig['AIRAVATA_SERVER'], $airavataconfig['AIRAVATA_PORT']);
	$transport->setSendTimeout($airavataconfig['AIRAVATA_TIMEOUT']);
	$protocol = new TBinaryProtocol($transport);
	$transport->open();
	$airavataclient = new AiravataClient($protocol);
	$outputs = get_experiment_outputs($expId);
	return $outputs;	
	var_dump($outputs);
	$transport->close();
}

function get_experiment_status($expId)
{
    	$airavataconfig = parse_ini_file("airavata-client-properties.ini");
	$transport = new TSocket($airavataconfig['AIRAVATA_SERVER'], $airavataconfig['AIRAVATA_PORT']);
	$transport->setSendTimeout($airavataconfig['AIRAVATA_TIMEOUT']);
	$protocol = new TBinaryProtocol($transport);
	$transport->open();
	$airavataclient = new AiravataClient($protocol);

	    try
	    {
		$experimentStatus = $airavataclient->getExperimentStatus($expId);
	    }
	    catch (InvalidRequestException $ire)
	    {
		echo 'InvalidRequestException!<br><br>' . $ire->getMessage();
	    }
	    catch (ExperimentNotFoundException $enf)
	    {
		echo 'ExperimentNotFoundException!<br><br>' . $enf->getMessage();
	    }
	    catch (AiravataClientException $ace)
	    {
		echo 'AiravataClientException!<br><br>' . $ace->getMessage();
	    }
	    catch (AiravataSystemException $ase)
	    {
		echo 'AiravataSystemException!<br><br>' . $ase->getMessage();
	    }
	    catch (\Exception $e)
	    {
		echo 'Exception!<br><br>' . $e->getMessage();
	    }

	    return ExperimentState::$__names[$experimentStatus->experimentState];
}


function getExperimentStatus($expId){
	$airavataconfig = parse_ini_file("airavata-client-properties.ini");
	$transport = new TSocket($airavataconfig['AIRAVATA_SERVER'], $airavataconfig['AIRAVATA_PORT']);
	$transport->setSendTimeout($airavataconfig['AIRAVATA_TIMEOUT']);
	$protocol = new TBinaryProtocol($transport);
	$transport->open();
	$airavataclient = new AiravataClient($protocol);
	$experimentStatusString = get_experiment_status($expId);
	return $experimentStatusString;
	$transport->close();
}

?>
