<?php
    // Check which ISP we're requesting usage for -- order counts! If one does't match it falls through to the next, teksavvy is the catchall.
    if (preg_match("/^[a-z0-9_\-\.]{3,}@(data\.com|ebox\.com|electronicbox\.net|highspeed\.com|internet\.com|ppp\.com|www\.com)$/", $_POST['APIKey'])) { 
        $data = GetUsageElectronicbox('Residential DSL');
    } else if (preg_match("/^[a-z0-9_\-\.]{3,}@ebox-business\.com$/", $_POST['APIKey'])) { 
        $data = GetUsageElectronicbox('Business DSL');
    } else if (preg_match("/^vl[a-z]{6}$/", $_POST['APIKey'])) {
        $data = GetUsageVideotron();
    } else if (preg_match("/^[1-9]\d{4}$/", $_POST['APIKey'])) {
        // Valid logins are [a-z0-9]{3,20}@caneris (no .com on the end), but usage is retrieved by 5 digit account number.
        $data = GetUsageCaneris();
    } else if (preg_match("/^[A-Z0-9]{7}[A-F0-9]{11}D@(start\.ca)$/", $_POST['APIKey'])) {
        $data = GetUsageStart('DSL');
    } else if (preg_match("/^[A-Z0-9]{7}[A-F0-9]{11}C@(start\.ca)$/", $_POST['APIKey'])) {
        $data = GetUsageStart('Cable');
    } else if (preg_match("/^[A-Z0-9]{7}[A-F0-9]{11}W@(start\.ca)$/", $_POST['APIKey'])) {
        $data = GetUsageStart('Wireless');
    } else if (preg_match("/^[A-Z0-9]{7}[A-F0-9]{11}D@(logins\.ca)$/", $_POST['APIKey'])) {
        $data = GetUsageStart('Wholesale DSL');
    } else if (preg_match("/^[A-Z0-9]{7}[A-F0-9]{11}C@(logins\.ca)$/", $_POST['APIKey'])) {
        $data = GetUsageStart('Wholesale Cable');
    } else if (preg_match("/^[A-Z0-9]{7}[A-F0-9]{11}W@(logins\.ca)$/", $_POST['APIKey'])) {
        $data = GetUsageStart('Wholesale Wireless');
    } else if (preg_match("/^([0-9A-F]{32})(|@teksavvy.com)(|\+[0-9]{1,4})$/", $_POST['APIKey'])) {
        $data = GetUsageTekSavvy();
    } else {
        $data["ISP"] = 'Invalid Username / API Key';
        ReturnError($data);
    }
	
	$DayOfMonth = date("j");
	$HourOfDay = date("G");
	$MinuteOfHour = date("i");
	$DaysInMonth = date("t");
	$TodayFraction = $data["RealTime"] ? ($HourOfDay / 24) + ($MinuteOfHour / 1440) : 0;

	// Populate the actual data
    $data["PaidT"] = $data["PaidD"] + $data["PaidU"];
    $data["FreeT"] = $data["FreeD"] + $data["FreeU"];
    $data["AllD"] = $data["PaidD"] + $data["FreeD"];
    $data["AllU"] = $data["PaidU"] + $data["FreeU"];
    $data["AllT"] = $data["PaidT"] + $data["FreeT"];

    // Populate the average data
    $data["PaidDA"] = GetAverage($data["PaidD"]);
    $data["PaidUA"] = GetAverage($data["PaidU"]);
    $data["PaidTA"] = GetAverage($data["PaidT"]);
    $data["FreeDA"] = GetAverage($data["FreeD"]);
    $data["FreeUA"] = GetAverage($data["FreeU"]);
    $data["FreeTA"] = GetAverage($data["FreeT"]);
    $data["AllDA"] = GetAverage($data["AllD"]);
    $data["AllUA"] = GetAverage($data["AllU"]);
    $data["AllTA"] = GetAverage($data["AllT"]);

    // Populate the predicted data
    $data["PaidDP"] = $data["PaidDA"] * $DaysInMonth;
    $data["PaidUP"] = $data["PaidUA"] * $DaysInMonth;
    $data["PaidTP"] = $data["PaidTA"] * $DaysInMonth;
    $data["FreeDP"] = $data["FreeDA"] * $DaysInMonth;
    $data["FreeUP"] = $data["FreeUA"] * $DaysInMonth;
    $data["FreeTP"] = $data["FreeTA"] * $DaysInMonth;
    $data["AllDP"] = $data["AllDA"] * $DaysInMonth;
    $data["AllUP"] = $data["AllUA"] * $DaysInMonth;
    $data["AllTP"] = $data["AllTA"] * $DaysInMonth;

    // Format the data
    $data["PaidD"] = formatBytes($data["PaidD"], 2);
    $data["PaidU"] = formatBytes($data["PaidU"], 2);
    $data["PaidT"] = formatBytes($data["PaidT"], 2);
    $data["PaidDA"] = formatBytes($data["PaidDA"], 2);
    $data["PaidUA"] = formatBytes($data["PaidUA"], 2);
    $data["PaidTA"] = formatBytes($data["PaidTA"], 2);
    $data["PaidDP"] = formatBytes($data["PaidDP"], 2);
    $data["PaidUP"] = formatBytes($data["PaidUP"], 2);
    $data["PaidTP"] = formatBytes($data["PaidTP"], 2);
    $data["FreeD"] = formatBytes($data["FreeD"], 2);
    $data["FreeU"] = formatBytes($data["FreeU"], 2);
    $data["FreeT"] = formatBytes($data["FreeT"], 2);
    $data["FreeDA"] = formatBytes($data["FreeDA"], 2);
    $data["FreeUA"] = formatBytes($data["FreeUA"], 2);
    $data["FreeTA"] = formatBytes($data["FreeTA"], 2);
    $data["FreeDP"] = formatBytes($data["FreeDP"], 2);
    $data["FreeUP"] = formatBytes($data["FreeUP"], 2);
    $data["FreeTP"] = formatBytes($data["FreeTP"], 2);
    $data["AllD"] = formatBytes($data["AllD"], 2);
    $data["AllU"] = formatBytes($data["AllU"], 2);
    $data["AllT"] = formatBytes($data["AllT"], 2);
    $data["AllDA"] = formatBytes($data["AllDA"], 2);
    $data["AllUA"] = formatBytes($data["AllUA"], 2);
    $data["AllTA"] = formatBytes($data["AllTA"], 2);
    $data["AllDP"] = formatBytes($data["AllDP"], 2);
    $data["AllUP"] = formatBytes($data["AllUP"], 2);
    $data["AllTP"] = formatBytes($data["AllTP"], 2);

    $data["Success"] = true;
    
	echo json_encode($data);





// From: http://stackoverflow.com/a/2510540/342378
function formatBytes($size, $precision = 2)
{
	if ($size <= 0) return "0 B";

	$base = log($size) / log(1024);
	$suffixes = array('B', 'KB', 'MB', 'GB', 'TB');

	return round(pow(1024, $base - floor($base)), $precision) . ' ' . $suffixes[floor($base)];
}

function GetAverage($amount)
{
	global $data, $DayOfMonth, $TodayFraction;
	if ($amount == 0) {
		return 0;
	} else if (($DayOfMonth == 1) && !$data["RealTime"]) {
		return 0;
	} else {
		return $amount / ($DayOfMonth - 1 + $TodayFraction);
	}
}

function GetUsageCaneris()
{
    $data["ISP"] = "Caneris DSL (unsupported)";
    ReturnError($data);
}

function GetUsageElectronicbox($type)
{
    $data["ISP"] = "Electronicbox $type (unsupported)";
    ReturnError($data);
}

function GetUsageStart($type)
{
    $data["ISP"] = "Start $type";
    $data["FreePeriod"] = true;
    $data["RealTime"] = true;
    $data["Uploads"] = false;
        
    $Usage = file_get_contents('http://www.start.ca/support/capsavvy?code=' . $_POST['APIKey']);
    if ($Usage == "ERROR") ReturnError($data);
    
	$KeyValues = explode(",", $Usage);
    if (count($KeyValues) < 6) ReturnError($data);

    for ($i = 0; $i < count($KeyValues); $i++) {
        if (strpos($KeyValues[$i], '=') !== false) {
            $Key = explode("=", $KeyValues[$i])[0];
            $Value = explode("=", $KeyValues[$i])[1];
            switch ($Key) {
                case 'DL': $data["PaidD"] = $Value; break;
                case 'UL': $data["PaidU"] = $Value; break;
                case 'DLFREE': $data["FreeD"] = $Value; break;
                case 'ULFREE': $data["FreeU"] = $Value; break;
            }
        }
    }
    
    return $data;
}

function GetUsageTekSavvy()
{
    $data["ISP"] = "TekSavvy (unsupported)";
    ReturnError($data);
}

function GetUsageVideotron()
{
    $data["ISP"] = "Videotron TPIA (unsupported)";
    ReturnError($data);
}


function ReturnError($data)
{
    $data["Success"] = false;
    echo json_encode($data);
    exit;
}
?>
