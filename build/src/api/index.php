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



    // $app->add(new \Tuupola\Middleware\Cors([
    //     "origin" => ["*"],
    //     "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE"],
    //     "headers.allow" => [],
    //     "headers.expose" => [],
    //     "credentials" => false,
    //     "cache" => 0,
    // ]));

        // $app->add(new \Tuupola\Middleware\Cors([
        //     "origin" => ["*"],
        //     "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE"],
        //     "headers.allow" => ["Accept", "Content-Type"],
        //     "headers.expose" => [],
        //     "credentials" => false,
        //     "cache" => 0,
        //     "cache" => 86400
        // ]));

    // $corsOptions = array("origin" => "*");
    // $app->post('/loginManager/logout/',\CorsSlim\CorsSlim::routeMiddleware($corsOptions) ,function() use ($app, $pdo, $db) { 
    //     logout($app, $pdo, $db); 
    // });

// $corsOptions2 = array(
//     "origin" => array('http://one.allowed-origin.com', 'http://two.allowed-origin.com'),
//     "exposeHeaders" => array("X-My-Custom-Header", "X-Another-Custom-Header"),
//     "maxAge" => 1728000,
//     "allowCredentials" => True,
//     "allowMethods" => array("POST, GET"),
//     "allowHeaders" => array("X-PINGOTHER")
//     );
// $cors = new \CorsSlim\CorsSlim($corsOptions);

    $corsOptions = array(
                        "origin" => "*",
                        // "origin" => array('http://172.16.148.126', 'http://two.allowed-origin.com'),
                        "exposeHeaders" => array("X-My-Custom-Header", "X-Another-Custom-Header"),
                        "maxAge" => 1728000,
                        "allowCredentials" => True,
                        "allowMethods" => array("POST", "GET"),
                        "allowHeaders" => array("Accept", "Content-Type", "X-PINGOTHER")
                    );

    $app->add(new \CorsSlim\CorsSlim($corsOptions));



    /* Connect Database Manager Partial : Localhost */
    $conn_db2 = "";
        $dsn = "mysql:dbname=rmr_db;host=localhost;charset=UTF8";
    $username = "root";
    $password = "";
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
    $app->post('/wlmaManager/getFlowPressureWithLocation/',function() use ($app, $pdo, $db, $conn_db2) { getFlowPressureWithLocation($app, $pdo, $db, $conn_db2); });
    $app->get('/wlmaManager/reportDMWithLocation/',function() use ($app, $pdo, $db, $conn_db2) { reportDMWithLocation($app, $pdo, $db, $conn_db2); });



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






    /* rtuInformationGeoJSON Partial : Development */
            /* ************************* */
        /* เริ่มกระบวนการเชื่อมต่อฐานข้อมูล DB2 ของ WLMA */
        /* ************************* */
        $reports = array();

	       $reports[] = array(
	            "date" => date("Y-m-d"),
	            "dm_name" => $postDM,
	            "flow_value" => (string)rand(0,200),
	            "pressure_value" => (string)rand(0,20)
	        );











        /* ************************* */
        /* เริ่มกระบวนการส่งค่ากลับ */
        /* ************************* */
        $resultText = "success";

        $reportResult = array("result" =>  $resultText, "rows" => $reports);
        
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($reportResult);



    };
        /**
     *
     * @apiName GetFlowPressureWithLocation
     * @apiGroup Wlma Manager
     * @apiVersion 0.1.0
     *
     * @api {post} /wlmaManager/getFlowPressureWithLocation/ Get Flow Pressure With Location
     * @apiDescription คำอธิบาย : ในส่วนนี้จะมีหน้าที่แสดงค่า Flow, Pressure และพิกัด lat, lng
     *
     *
     * @apiParam {String} name     New name of the user
     *
     * @apiSampleRequest /wlmaManager/getFlowPressureWithLocation/
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

    function getFlowPressureWithLocation($app, $pdo, $db, $conn_db2) {

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
            // $postDM = $result->paramDM;


        } else if ($ContetnType == "application/x-www-form-urlencoded"){

            //$userID = $app->request()->params('userID_param');
            //$userID = $app->request()->post('userID_param');
        }






    /* rtuInformationGeoJSON Partial : Development */
            /* ************************* */
        /* เริ่มกระบวนการเชื่อมต่อฐานข้อมูล DB2 ของ WLMA */
        /* ************************* */

        // $reports = array();

	       // $reports[] = array(
	       //      "dm_name" => "DM-01-01-01-01",
	       //      "lat" => "",
	       //      "lng" => "",
	       //      "flow_value" => (string)rand(0,200),
	       //      "flow_log_dt" => date("Y-m-d"),
	       //      "pressure_value" => (string)rand(0,20),
	       //      "pressure_log_dt" => date("Y-m-d")
	       //  );

	       // $reports[] = array(
	       //      "dm_name" => "DM-01-01-01-02",
	       //      "lat" => "",
	       //      "lng" => "",
	       //      "flow_value" => (string)rand(0,200),
	       //      "flow_log_dt" => date("Y-m-d"),
	       //      "pressure_value" => (string)rand(0,20),
	       //      "pressure_log_dt" => date("Y-m-d")
	       //  );

        $reports = array();

        $results = $db->rtu_main_tb->where("rtu_status = 1")->order("dm_code ASC");
        
        foreach ($results as $result) {

            $result_rtu_pin_code = $db->rtu_pin_code_tb->where("dm_code = ? and enable = 1", $result["dm_code"])->fetch();

            $reports[] = array(
            	"dm_name" => $result["dm_code"],
            	"lat" => $result_rtu_pin_code["lat"],
            	"lng" => $result_rtu_pin_code["lng"],
            	"flow_value" => (string)rand(0,200),
            	"flow_log_dt" => date("Y-m-d"),
            	"pressure_value" => (string)rand(0,20),
            	"pressure_log_dt" => date("Y-m-d")
                );

        }

	$rowCount = count($results);








        /* ************************* */
        /* เริ่มกระบวนการส่งค่ากลับ */
        /* ************************* */
        $resultText = "success";

        $reportResult = array("result" =>  $resultText, 
                              "count" => $rowCount,
                              "rows" => $reports);
        
        $app->response()->header("Content-Type", "application/json");
        echo json_encode($reportResult);



    };
        /**
     *
     * @apiName ReportDMWithLocation
     * @apiGroup Wlma Manager
     * @apiVersion 0.1.0
     *
     * @api {get} /wlmaManager/reportDMWithLocation/ Report DM With Location
     * @apiDescription คำอธิบาย : ในส่วนนี้จะมีหน้าที่แสดงค่า Flow, Pressure และพิกัด lat, lng
     *
     *
     * @apiParam {String} name     New name of the user
     *
     * @apiSampleRequest /wlmaManager/reportDMWithLocation/
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

    function reportDMWithLocation($app, $pdo, $db, $conn_db2) {

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
            // $postDM = $result->paramDM;


        } else if ($ContetnType == "application/x-www-form-urlencoded"){

            //$userID = $app->request()->params('userID_param');
            //$userID = $app->request()->post('userID_param');
        }






    /* rtuInformationGeoJSON Partial : Development */
            /* ************************* */
        /* เริ่มกระบวนการเชื่อมต่อฐานข้อมูล DB2 ของ WLMA */
        /* ************************* */

        // $reports = array();

	       // $reports[] = array(
	       //      "dm_name" => "DM-01-01-01-01",
	       //      "lat" => "",
	       //      "lng" => "",
	       //      "flow_value" => (string)rand(0,200),
	       //      "flow_log_dt" => date("Y-m-d"),
	       //      "pressure_value" => (string)rand(0,20),
	       //      "pressure_log_dt" => date("Y-m-d")
	       //  );

	       // $reports[] = array(
	       //      "dm_name" => "DM-01-01-01-02",
	       //      "lat" => "",
	       //      "lng" => "",
	       //      "flow_value" => (string)rand(0,200),
	       //      "flow_log_dt" => date("Y-m-d"),
	       //      "pressure_value" => (string)rand(0,20),
	       //      "pressure_log_dt" => date("Y-m-d")
	       //  );

        $reports = array();

        $results = $db->rtu_main_tb->where("rtu_status = 1")->order("dm_code ASC");
        
        foreach ($results as $result) {

            $result_rtu_pin_code = $db->rtu_pin_code_tb->where("dm_code = ? and enable = 1", $result["dm_code"])->fetch();

            $reports[] = array(
            	"dm_name" => $result["dm_code"],
            	"lat" => $result_rtu_pin_code["lat"],
            	"lng" => $result_rtu_pin_code["lng"],
            	"flow_value" => (string)rand(0,200),
            	"flow_log_dt" => date("Y-m-d"),
            	"pressure_value" => (string)rand(0,20),
            	"pressure_log_dt" => date("Y-m-d")
                );

        }

	$rowCount = count($results);








        /* ************************* */
        /* เริ่มกระบวนการส่งค่ากลับ */
        /* ************************* */
        $resultText = "success";

        $reportResult = array("result" =>  $resultText, 
                              "count" => $rowCount,
                              "rows" => $reports);
        
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