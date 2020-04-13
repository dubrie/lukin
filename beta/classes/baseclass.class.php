<?
class baseclass {
    function setData($varName, $value) {
        $this->$varName=$value;
    }

    function getData($varName) {
        return $this->$varName;
    }

    function reset() {
        $vars = get_class_vars(get_class($this));
        foreach ($vars as $name => $val) {
            $this->$name = '';
        }
    }

    function url_exists($url) {
        $handle   = curl_init($url);
        if (false === $handle) {
            error_log('Fail to start Curl session');
        }

        curl_setopt($handle, CURLOPT_HEADER, false);
        curl_setopt($handle, CURLOPT_NOBODY, true);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, false);

        // grab Url
        $connectable = curl_exec($handle);

	    $intReturnCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
	    curl_close ($handle);

	    if ($intReturnCode != 200) {
			error_log("url doesn't exist");
	    	return false;
	    } else {
			error_log("url exists");
	       return true ;
	    }
	}

}
?>
