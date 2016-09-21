<?php

    date_default_timezone_set("Asia/Bangkok");

    require_once '../../packages/autoload.php';


    /* ****************** */
    /* Slim framework 2.x */
    /* ****************** */
    
    // $app = new \Slim\Slim();

    $logWriter = new \Slim\LogWriter(fopen('./api-debug.log', 'a'));
    $app = new \Slim\Slim(array('log.enabled' => true,
                                'log.writer' => $logWriter,
                                'debug' => true));




    /* Connect Database Manager Partial : Production */
        // /* เชื่อมต่อ DB2 */
    $driver_db2 = "{IBM DB2 ODBC DRIVER}";
    $database_db2 = "iWLMA";
    $hostname_db2 = "172.16.194.210";
    $port_db2 = 50000;
    $user_db2 = "db2admin";
    $password_db2 = "password";

    $conn_string_db2 = "DRIVER=$driver_db2;DATABASE=$database_db2;";
    $conn_string_db2 .= "HOSTNAME=$hostname_db2;PORT=$port_db2;PROTOCOL=TCPIP;";
    $conn_string_db2 .= "UID=$user_db2;PWD=$password_db2;";

    try {
        $conn_db2 = db2_connect($conn_string_db2, '', '');

        $client = db2_client_info($conn_db2);

        // if ($client) {
        //     echo var_dump($client->APPL_CODEPAGE);
        //     echo "DRIVER_NAME: ";           var_dump( $client->DRIVER_NAME );
        //     echo "DRIVER_VER: ";            var_dump( $client->DRIVER_VER );
        //     echo "DATA_SOURCE_NAME: ";      var_dump( $client->DATA_SOURCE_NAME );
        //     echo "DRIVER_ODBC_VER: ";       var_dump( $client->DRIVER_ODBC_VER );
        //     echo "ODBC_VER: ";              var_dump( $client->ODBC_VER );
        //     echo "ODBC_SQL_CONFORMANCE: ";  var_dump( $client->ODBC_SQL_CONFORMANCE );
        //     echo "APPL_CODEPAGE: ";         var_dump( $client->APPL_CODEPAGE );
        //     echo "CONN_CODEPAGE: ";         var_dump( $client->CONN_CODEPAGE );
        // } else {
        //     echo "Error";
        // }


        if(!$conn_db2) {
            echo db2_conn_errormsg();
        } else {
            //echo "Hello World, from the IBM_DB2 PHP extension!";
            //db2_close($conn_db2);
        }
    } 
    catch (Exception $e) {
        //echo $e;
    }
        /* เชื่อมต่อ MySQL บนเครื่อง 172.16.194.210 (http://wlma-mt.wms.mwa/) */
    $dsn = "mysql:dbname=rmr_db;host=localhost;charset=UTF8";
    $username = "root";
    $password = "P@ssw0rd";
    $pdo = new PDO($dsn, $username, $password);
    $db = new NotORM($pdo);



    /* Test manager */
    $app->get('/hello/:name',function($name) use ($app) { 
        getHello($app, $name); 
    });

    /* WLMA manager */
    $app->post('/wlmaManager/checkUserPasswordFromWLMA/',function() use ($app, $pdo, $conn_db2) { checkUserPasswordFromWLMA($app, $pdo, $conn_db2); });
    $app->post('/wlmaManager/reportPressureAverage/',function() use ($app, $pdo, $conn_db2) { reportPressureAverage($app, $pdo, $conn_db2); });
    $app->post('/wlmaManager/reportWLMA1125/',function() use ($app, $pdo, $conn_db2) { reportWLMA1125($app, $pdo, $conn_db2); });
    $app->post('/wlmaManager/reportWaterLeakageByDMA/',function() use ($app, $pdo, $conn_db2) { reportWaterLeakageByDMA($app, $pdo, $conn_db2); });
    $app->post('/wlmaManager/getFlowPressureByDM/',function() use ($app, $pdo, $conn_db2) { getFlowPressureByDM($app, $pdo, $conn_db2); });




    /* WLMA Manager Partial */
    	/**
	 *
	 * @apiName CheckUserPasswordFromWLMA
	 * @apiGroup Wlma Manager
	 * @apiVersion 0.1.0
	 *
	 * @api {post} /wlmaManager/checkUserPasswordFromWLMA/ Check User Password from WLMA
	 * @apiDescription คำอธิบาย : ในส่วนนี้จะมีหน้าที่ตรวจสอบสิทธิ์การเข้าใช้งานระบบ โดยจะเป็นการส่งค่า User & Password ไปตรวจสอบที่ฐานข้อมูลในระบบ WLMA
	 *
	 *
	 * @apiSampleRequest /wlmaManager/checkUserPasswordFromWLMA/
	 *
	 * @apiSuccess {String} msg แสดงข้อความทักทายผู้ใช้งาน
	 *
	 * @apiSuccessExample Example data on success:
	 * {
	 *   "msg": "Hello, anusorn"
	 * }
	 *
	 * @apiError UserNotFound The <code>id</code> of the User was not found.
	 * @apiErrorExample {json} Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "error": "UserNotFound"
	 *     }
	 *
	 */
	 function checkUserPasswordFromWLMA($app, $pdo, $conn_db2) {

	    /* ************************* */
        /* เริ่มกระบวนการเชื่อมต่อฐานข้อมูล DB2 ของ WLMA */
        /* ************************* */
        $reports = array();

        $sql = "select * from AUTH_USER_INFO order by USER_ID";

        if ($conn_db2) {
            // # code...
            $stmt = db2_exec($conn_db2, $sql);

            while ($row = db2_fetch_array($stmt)) {
                
                $userID = iconv("TIS-620", "UTF-8",$row[0]);
                $name = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row[1]);

                $reports[] = array(
                	"USER_ID" => $userID,
                	"NAME" => $name
                );
            }
        }

        $rowCount = count($reports);

        /* ************************* */
        /* เริ่มกระบวนการส่งค่ากลับ */
        /* ************************* */
        $resultText = "success";

        $reportResult = array("result" =>  $resultText, "msg" => "สวัสดี, $name", "count" => $rowCount, "rows" => $reports);
        //$reportResult = array("result" =>  $resultText, "msg" => "สวัสดี, $name");
        //$reportResult = array("result" =>  $resultText);
        
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($reportResult);

	 }
	 
    	/**
	 *
	 * @apiName ReportPressureAverage
	 * @apiGroup Wlma Manager
	 * @apiVersion 0.1.0
	 *
	 * @api {post} /wlmaManager/reportPressureAverage/ Report Pressure Average by DMA
	 * @apiDescription คำอธิบาย : ในส่วนนี้จะมีหน้าที่แสดงรายงานแรงดันเฉลี่ยแยกตามแต่ละ DMA ในช่วงวันที่กำหนด
	 *
	 *
	 * @apiSampleRequest /wlmaManager/reportPressureAverage/
	 *
	 * @apiSuccess {String} msg แสดงข้อความทักทายผู้ใช้งาน
	 *
	 * @apiSuccessExample Example data on success:
	 * {
	 *   "msg": "Hello, anusorn"
	 * }
	 *
	 * @apiError UserNotFound The <code>id</code> of the User was not found.
	 * @apiErrorExample {json} Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "error": "UserNotFound"
	 *     }
	 *
	 */
	 function reportPressureAverage($app, $pdo, $conn_db2) {

	 	/* ************************* */
        /* เริ่มกระบวนการรับค่าพารามิเตอร์จากส่วนของ Payload ซึ่งอยู่ในรูปแบบ JSON */
        /* ************************* */
        $headers = $app->request->headers;
        $ContetnType = $app->request->headers->get('Content-Type');

        /**
        * apidoc @apiSampleRequest, iOS RESTKit use content-type is "application/json"
        * Web Form, Advance REST Client App use content-type is "application/x-www-form-urlencoded"
        */
        if (($ContetnType == "application/json") || ($ContetnType == "application/json; charset=utf-8") ) {

	        $request = $app->request();
	        $result = json_decode($request->getBody());

	        /* receive request */
	        $postStartDate = $result->startDate;
	        $postEndDate = $result->endDate;


		} else if ($ContetnType == "application/x-www-form-urlencoded"){

		    //$userID = $app->request()->params('userID_param');
		    //$userID = $app->request()->post('userID_param');
		}


	    /* ************************* */
        /* เริ่มกระบวนการเชื่อมต่อฐานข้อมูล DB2 ของ WLMA */
        /* ************************* */
        $reports = array();

        $sql = "select to_char(log_dt, 'YYYY-MM-DD') as Date, area_code, avg(p) as AveragePressure from core_area, meter_hist 
where meter_code in (select meter_code from core_area_meter where core_area_meter.area_code = core_area.area_code and meter_inout='I')
and log_dt between timestamp('".$postStartDate."') and timestamp('".$postEndDate." 23:59:00')
and to_char(log_dt, 'HH24') between '05' and '09'
and area_axis_code = 'D'
group by area_code, to_char(log_dt, 'YYYY-MM-DD')";

        if ($conn_db2) {
            // # code...
            $stmt = db2_exec($conn_db2, $sql);

            while ($row = db2_fetch_array($stmt)) {
                
                $tmpDate = iconv("TIS-620", "UTF-8",$row[0]);
                $tmpAreaCode = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row[1]);
                $tmpAveragePressure = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row[2]);

                $reports[] = array(
                	"date" => $tmpDate,
                	"area_code" => "DMA-".$tmpAreaCode,
                	"average_pressure" => $tmpAveragePressure
                );
            }
        }

        $rowCount = count($reports);

        /* ************************* */
        /* เริ่มกระบวนการส่งค่ากลับ */
        /* ************************* */
        $resultText = "success";

        $reportResult = array("result" =>  $resultText, "count" => $rowCount, "rows" => $reports);
        //$reportResult = array("result" =>  $resultText, "msg" => "สวัสดี, $name");
        //$reportResult = array("result" =>  $resultText);
        
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($reportResult);

	 }
	 
    	/**
	 *
	 * @apiName ReportWLMA1125
	 * @apiGroup Wlma Manager
	 * @apiVersion 0.1.0
	 *
	 * @api {post} /wlmaManager/reportWLMA1125/ Report WLMA-1125
	 * @apiDescription คำอธิบาย : ในส่วนนี้จะมีหน้าที่แสดงรายงานสถานะการเชื่อมโยงระหว่าง WLMA กับ 1125
	 *
	 *
	 * @apiSampleRequest /wlmaManager/reportWLMA1125/
	 *
	 * @apiSuccess {String} msg แสดงข้อความทักทายผู้ใช้งาน
	 *
	 * @apiSuccessExample Example data on success:
	 * {
	 *   "msg": "Hello, anusorn"
	 * }
	 *
	 * @apiError UserNotFound The <code>id</code> of the User was not found.
	 * @apiErrorExample {json} Error-Response:
	 *     HTTP/1.1 404 Not Found
	 *     {
	 *       "error": "UserNotFound"
	 *     }
	 *
	 */
	 function reportWLMA1125($app, $pdo, $conn_db2) {

	 	/* ************************* */
        /* เริ่มกระบวนการรับค่าพารามิเตอร์จากส่วนของ Payload ซึ่งอยู่ในรูปแบบ JSON */
        /* ************************* */
        $headers = $app->request->headers;
        $ContetnType = $app->request->headers->get('Content-Type');

        /**
        * apidoc @apiSampleRequest, iOS RESTKit use content-type is "application/json"
        * Web Form, Advance REST Client App use content-type is "application/x-www-form-urlencoded"
        */
        if ($ContetnType == "application/json") {

	        $request = $app->request();
	        $result = json_decode($request->getBody());

	        /* receive request */
	        $postStartDate = $result->startDate;
	        $postEndDate = $result->endDate;


		} else if ($ContetnType == "application/x-www-form-urlencoded"){

		    //$userID = $app->request()->params('userID_param');
		    //$userID = $app->request()->post('userID_param');
		}


	    /* ************************* */
        /* เริ่มกระบวนการเชื่อมต่อฐานข้อมูล DB2 ของ WLMA */
        /* ************************* */
        $reports = array();

        // เปิดงาน
        $sql_open_only = "select 	JOB_code, 
        				BRANCH_CODE, 
        				AREA_CODE, 
        				JOB_OPEN_DT, 
        				JOB_CLOSE_DT, 
        				JOB_BEG_DT, 
        				JOB_END_DT, 
        				JOB_STATUS, 
        				CSS_CODE, 
        				REQUEST_CODE 
						from FSM_MAIN
						where job_open_dt between timestamp ('2015-11-01') and timestamp ('2015-11-30')";



        if ($conn_db2) {
            // OPEN Only
            $stmt_open_only = db2_exec($conn_db2, $sql_open_only);

            while ($row = db2_fetch_both($stmt_open_only)) {
           

                $tmpJOB_code = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row["JOB_CODE"]);
				$tmpBRANCH_CODE = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['BRANCH_CODE']); 
				$tmpAREA_CODE = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['AREA_CODE']); 
				$tmpJOB_OPEN_DT = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['JOB_OPEN_DT']); 
				$tmpJOB_CLOSE_DT = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['JOB_CLOSE_DT']); 
				$tmpJOB_BEG_DT = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['JOB_BEG_DT']); 
				$tmpJOB_END_DT = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['JOB_END_DT']); 
				$tmpJOB_STATUS = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['JOB_STATUS']); 
				$tmpCSS_CODE = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['CSS_CODE']); 
				$tmpREQUEST_CODE = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['REQUEST_CODE']);
				$tmpPIPE_SIZE_CODE = "";
				$tmpPIPE_MATERIAL_CODE = "";

                $reports[] = array(
                	"JOB_CODE" => $tmpJOB_code,
                  	"BRANCH_CODE" => $tmpBRANCH_CODE,
                  	"AREA_CODE" => $tmpAREA_CODE,
                  	"JOB_OPEN_DT" => $tmpJOB_OPEN_DT,
                  	"JOB_CLOSE_DT" => $tmpJOB_CLOSE_DT,
                  	"JOB_BEG_DT" => $tmpJOB_BEG_DT,
                  	"JOB_END_DT" => $tmpJOB_END_DT,
                  	"JOB_STATUS" => $tmpJOB_STATUS,
                  	"CSS_CODE" => $tmpCSS_CODE,
                  	"REQUEST_CODE" => $tmpREQUEST_CODE,
                  	"PIPE_SIZE_CODE" => $tmpPIPE_SIZE_CODE,
                  	"PIPE_MATERIAL_CODE" => $tmpPIPE_MATERIAL_CODE,
                  	"TYPE" => "OPEN"
                );
            }


        // Close Only
		$sql_close_only = "select FMH.JOB_code, 
						FMH.BRANCH_CODE, 
						FMH.AREA_CODE, 
						FMH.JOB_OPEN_DT, 
						FMH.JOB_CLOSE_DT, 
						FMH.JOB_BEG_DT, 
						FMH.JOB_END_DT, 
						FMH.JOB_STATUS, 
						FMH.CSS_CODE, 
						FMH.REQUEST_CODE, 
						LR.PIPE_SIZE_CODE, 
						LR.PIPE_MATERIAL_CODE
						from FSM_MAIN_HIST FMH, fsm_lr LR
						where FMH.JOB_code = LR.JOB_CODE and job_open_dt between timestamp ('2015-11-01') and timestamp ('2015-11-30')";

           
            $stmt_close_only = db2_exec($conn_db2, $sql_close_only);

            while ($row = db2_fetch_both($stmt_close_only)) {
           

                $tmpJOB_code = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row["JOB_CODE"]);
				$tmpBRANCH_CODE = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['BRANCH_CODE']); 
				$tmpAREA_CODE = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['AREA_CODE']); 
				$tmpJOB_OPEN_DT = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['JOB_OPEN_DT']); 
				$tmpJOB_CLOSE_DT = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['JOB_CLOSE_DT']); 
				$tmpJOB_BEG_DT = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['JOB_BEG_DT']); 
				$tmpJOB_END_DT = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['JOB_END_DT']); 
				$tmpJOB_STATUS = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['JOB_STATUS']); 
				$tmpCSS_CODE = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['CSS_CODE']); 
				$tmpREQUEST_CODE = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['REQUEST_CODE']);
				$tmpPIPE_SIZE_CODE = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['PIPE_SIZE_CODE']);
				$tmpPIPE_MATERIAL_CODE = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row['PIPE_MATERIAL_CODE']);

                $reports[] = array(
                	"JOB_CODE" => $tmpJOB_code,
                  	"BRANCH_CODE" => $tmpBRANCH_CODE,
                  	"AREA_CODE" => $tmpAREA_CODE,
                  	"JOB_OPEN_DT" => $tmpJOB_OPEN_DT,
                  	"JOB_CLOSE_DT" => $tmpJOB_CLOSE_DT,
                  	"JOB_BEG_DT" => $tmpJOB_BEG_DT,
                  	"JOB_END_DT" => $tmpJOB_END_DT,
                  	"JOB_STATUS" => $tmpJOB_STATUS,
                  	"CSS_CODE" => $tmpCSS_CODE,
                  	"REQUEST_CODE" => $tmpREQUEST_CODE,
                  	"PIPE_SIZE_CODE" => $tmpPIPE_SIZE_CODE,
                  	"PIPE_MATERIAL_CODE" => $tmpPIPE_MATERIAL_CODE,
                  	"TYPE" => "CLOSED"
                );
            }
        }

        $rowCount = count($reports);

        /* ************************* */
        /* เริ่มกระบวนการส่งค่ากลับ */
        /* ************************* */
        $resultText = "success";

        $reportResult = array("result" =>  $resultText, "count" => $rowCount, "rows" => $reports);
        //$reportResult = array("result" =>  $resultText, "msg" => "สวัสดี, $name");
        //$reportResult = array("result" =>  $resultText);
        
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($reportResult);

	 }
	 
        /**
     *
     * @apiName ReportWaterLeakageByDMA
     * @apiGroup Wlma Manager
     * @apiVersion 0.1.0
     *
     * @api {post} /wlmaManager/reportWaterLeakageByDMA/ Report Water Leakage by DMA
     * @apiDescription คำอธิบาย : ในส่วนนี้จะมีหน้าที่แสดงรายงานปริมาณน้ำสูญเสียแยกตามแต่ละ DMA ในเดือนที่กำหนด
     *
     *
     * @apiParam {String} name     New name of the user
     *
     * @apiSampleRequest /wlmaManager/reportWaterLeakageByDMA/
     *
     * @apiSuccess {String} msg แสดงข้อความทักทายผู้ใช้งาน
     *
     * @apiSuccessExample Example data on success:
     * {
     *   "msg": "Hello, anusorn"
     * }
     *
     * @apiError UserNotFound The <code>id</code> of the User was not found.
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     *
     */

    function reportWaterLeakageByDMA($app, $pdo, $conn_db2) {

        /* ************************* */
        /* เริ่มกระบวนการเชื่อมต่อฐานข้อมูล DB2 ของ WLMA */
        /* ************************* */
        $reports = array();

        $sql = "call P_WB_WL_REPORT2('201604', '01')";

        if ($conn_db2) {
            // # code...
            $stmt = db2_exec($conn_db2, $sql);

            while ($row = db2_fetch_array($stmt)) {
                
                $tmpAREA_CODE = iconv("TIS-620", "UTF-8",$row[0]);
                $tmpAREA_STATUS = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row[1]);
                $tmpAREA_WL = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row[2]);

                $reports[] = array(
                    "area_code" => $tmpAREA_CODE,
                    "area_status" => $tmpAREA_STATUS,
                    "area_wl" => $tmpAREA_WL
                );
            }
        }

        $rowCount = count($reports);

        /* ************************* */
        /* เริ่มกระบวนการส่งค่ากลับ */
        /* ************************* */
        $resultText = "success";

        $reportResult = array("result" =>  $resultText, "count" => $rowCount, "rows" => $reports);
        //$reportResult = array("result" =>  $resultText, "msg" => "สวัสดี, $name");
        //$reportResult = array("result" =>  $resultText);
        
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($reportResult);

    };
        /**
     *
     * @apiName GetFlowPressureByDM
     * @apiGroup Wlma Manager
     * @apiVersion 0.1.0
     *
     * @api {post} /wlmaManager/getFlowPressureByDM/ GET Flow Pressure By DM (v 0.1.0)
     * @apiDescription คำอธิบาย : ในส่วนนี้จะมีหน้าที่แสดงค่า Flow, Pressure ตาม DM ที่กำหนด
     *
     *
     * @apiParam {String} name     New name of the user
     *
     * @apiSampleRequest /wlmaManager/getFlowPressureByDM/
     *
     * @apiSuccess {String} msg แสดงข้อความทักทายผู้ใช้งาน
     *
     * @apiSuccessExample Example data on success:
     * {
     *   "msg": "Hello, anusorn"
     * }
     *
     * @apiError UserNotFound The <code>id</code> of the User was not found.
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *       "error": "UserNotFound"
     *     }
     *
     */

    function getFlowPressureByDM($app, $pdo, $conn_db2) {

        /* ************************* */
        /* เริ่มกระบวนการรับค่าพารามิเตอร์จากส่วนของ Payload ซึ่งอยู่ในรูปแบบ JSON */
        /* ************************* */
        $headers = $app->request->headers;
        $ContetnType = $app->request->headers->get('Content-Type');

        /**
        * apidoc @apiSampleRequest, iOS RESTKit use content-type is "application/json"
        * Web Form, Advance REST Client App use content-type is "application/x-www-form-urlencoded"
        */
        if (($ContetnType == "application/json") || ($ContetnType == "application/json; charset=utf-8") ) {

            $request = $app->request();
            $result = json_decode($request->getBody());

            /* receive request */
            $postDM = $result->paramDM;


        } else if ($ContetnType == "application/x-www-form-urlencoded"){

            //$userID = $app->request()->params('userID_param');
            //$userID = $app->request()->post('userID_param');
        }






    /* rtuInformationGeoJSON Partial : Production */
            /* ************************* */
        /* เริ่มกระบวนการเชื่อมต่อฐานข้อมูล DB2 ของ WLMA */
        /* ************************* */
        $reports = array();



        $sql = "select meter_code, rtu_log_dt, log_type, value from METER_ONLINE_DATA_LAST where meter_code = '".$postDM."'";


        if ($conn_db2) {
            // # code...
            $stmt = db2_exec($conn_db2, $sql);

            $tmpDM = "";
            $tmpFlowVal = "-"; 
            $tmpPressureVal = "-";

            while ($row = db2_fetch_array($stmt)) {
                
                $tmpDM = iconv("TIS-620", "UTF-8",$row[0]);
                $tmpRtuLogDt = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row[1]);
                $tmpLogType = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row[2]);
                $tmpValue = iconv("TIS-620//IGNORE", "UTF-8//IGNORE",$row[3]);

                if ($tmpLogType == "F") {
                	$tmpFlowVal = $tmpValue;
                } else if ($tmpLogType == "P") {
                	$tmpPressureVal = $tmpValue;
                }


            }


	       $reports[] = array(
	            "date" => date("Y-m-d"),
	            "dm_name" => $tmpDM,
	            "flow_value" => (string)$tmpFlowVal,
	            "pressure_value" => (string)$tmpPressureVal
	        );
        }








        /* ************************* */
        /* เริ่มกระบวนการส่งค่ากลับ */
        /* ************************* */
        $resultText = "success";

        $reportResult = array("result" =>  $resultText, "rows" => $reports);
        
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($reportResult);



    };


	$app->run();


	/* Test Partial */
     /**
 *
 * @apiName HelloWorld
 * @apiGroup TEST SERVICE
 * @apiVersion 0.1.0
 *
 * @api {get} /hello/:name Test First Service (v 0.1.0)
 * @apiDescription คำอธิบาย : Hello World, Test Service
 *
 *
 * @apiSampleRequest /hello/:name
 *
 * @apiParam {String} name     New name of the user
 *
 * @apiSuccess {String} msg แสดงข้อความทักทายผู้ใช้งาน
 *
 * @apiSuccessExample Example data on success:
 * {
 *   "msg": "Hello, anusorn"
 * }
 *
 * @apiError UserNotFound The <code>id</code> of the User was not found.
 * @apiErrorExample {json} Error-Response:
 *     HTTP/1.1 404 Not Found
 *     {
 *       "error": "UserNotFound"
 *     }
 *
 */
 
 function getHello($app, $name) {
 	$return_m = array("msg" => "Hello, $name");
 	echo json_encode($return_m);
 }



?>