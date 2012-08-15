<?php

/******************************************
 * Debugging functions
 ******************************************/
 	function debug($item) {
 		if (DEBUG == TRUE) {
 			if (is_array($item)) {
 				array_debug($item);
 			} else {
 				if (!$item) {
 					$item = "NO VALUE";
 				}
 				
 				echo "<pre>$item</pre>";
 			}
 		}
 		return;	
 	}
 	
 	

/******************************************
 * Array functions
 ******************************************/

	/**
	* Displays array
	*
	* This is a debugging function that will show the structure of the passed array.
	* @param array $array Array to display.
	* @return null
	*/
	function array_debug() {
		$args  = func_get_args();
		$count = func_num_args();

		if ($count > 1) {
			if (end($args) == 1) {
				$exit = TRUE;
				array_pop($args);
			}
		}

		foreach ($args as $array) {
			if (is_array($array)) {
				echo "<pre>--- START\n";
				print_r($array);
				echo "\n--- END</pre>";
			} else {
				echo "<p><code>-- Invalid array --</code></p>";
			}
		}

		if ($exit) { exit(); }

		return;
	}


	/**
	* Sort a multidimensional array
	*
	* Sorts an associative array by a specified field/column and retains keys and structure.
	* Taken from: http://fr3.php.net/manual/en/function.array-multisort.php
	* @param string $array Array to sort
	* @param string $list All secondary parameters are considered fields to sort by, given priority by order
	* @return array Sorted array
	*/
	function array_csort() {
	     $args   = func_get_args();
	     $marray = array();

	     if (is_array($args[0]) and (count($args[0]) > 0)) {

		     $marray    = array_shift($args);
		     $msortline = "return(array_multisort(";
		     foreach ($args as $arg) {
		         $i++;
		         if (is_string($arg)) {
		             foreach ($marray as $row) {
		                 $a = strtoupper($row[$arg]);
		                 $sortarr[$i][] = $a;
		             }
		         } else {
		             $sortarr[$i] = $arg;
		         }
		         $msortline .= "\$sortarr[".$i."],";
		     }
		     $msortline .= "\$marray));";

		     eval($msortline);
		}

	    return($marray);
	}


	/**
	* Restore a saved array
	*
	* Reads the saved array flat file and restores it as an array
	* @param string $filepath Absolute path to save location
	* @return array Saved array as variable
	*/
	function array_load($filepath) {
		if (file_exists($filepath)) {
			$file = fopen($filepath, "r");
			$arraysource = fread($file, filesize($filepath));
			fclose($file);

			$thisarray = unserialize($arraysource);
		}

		if (!is_array($thisarray)) {
			$thisarray = array();
		}

		return($thisarray);
	}


	/**
	* Save an array to a text file
	*
	* Flattens the array and makes it safe for saving as a text file.
	* @param string $filepath Absolute path to save location
	* @param array $array Array to be saved
	* @return array Passed array
	*/
	function array_save($filepath,$array) {
		if (!is_array($array)) {
			$array = array();
		}

		$file = fopen($filepath,"w");
		fputs($file, serialize($array));
		fclose($file);

		return($array);
	}


	// Extract a single field into a standard array ** Recusrsion function **
	function array_extract() {
		$argcount = func_num_args();
		$args     = func_get_args();
		$list     = array();

		if (end($args) == 1) {
			$flat = TRUE;
			array_pop($args);
			$argcount = count($args);
		}

		if ($argcount > 1) {
			$field = $args[0];
			array_shift($args);

			for ($a=0; $a < ($argcount-1); $a++) {
				$item = $args[$a];

				if (is_array($item)) {
					foreach ($item as $key => $elem) {
						if (is_array($elem)) {
							$grab = array_extract($field, $elem); // Recursion
							$list = array_merge($list, $grab);
						} else {
							if ($key == $field) {
								$list[] = $elem;
							}
						}
					}
				}
			}
		}

		$list = array_unique($list);

		if ($flat) {
			$list = implode(",",$list);
		}

		return($list);
	}

	// Apply a function to each element in an array
	// Works on multiarray - Different than array_walk()
	// ** Calls array $elem by reference **
	function array_each(&$elem, $func) {
		if (!is_array($elem)) {
			$elem = $func($elem);
		} else {
			foreach ($elem as $key => $value) {
				$elem[$key] = array_each($value, $func);
			}
		}

		return($elem);
	}

/******************************************
 * MySQL
 ******************************************/

	/**
	* Inserts a new record
	*
	* Takes specified array and inserts data as a new record.
	* Interprets array key as field name and value as value.
	* @param string $table Table name
	* @param array $data Array of data to insert
	* @param int $debug Set as 1 to display query without executing
	* @return int Record insert ID
	*/
	function mysql_insert($data, $table, $debug=FALSE) {
		$sql = "insert into $table ";

		if (is_array($data)) {
			foreach ($data as $field => $value) {
				$fieldlist[] = $field;

				if (!is_numeric($value)) {
					$value = "'".addslashes($value)."'";
				}

				$valuelist[] = $value;
			}

			$fieldlist = implode(",",$fieldlist);
			$valuelist = implode(",",$valuelist);

			$sql = $sql. "($fieldlist) values ($valuelist)";
		} else {
			$debug = TRUE;
		}

		if ($debug == FALSE) {
			mysql_query($sql);
			$insertid = mysql_insert_id();
		} else {
			echo "<pre>$sql</pre>";
		}


		return($insertid);
	}

	/**
	* Updates an existing record
	*
	* Takes specified array and updates an existing record.
	* Interprets array key as field name and value as value.
	* @param string $table Table name
	* @param array $data Array of data to insert
	* @param string $condition Required condition to update query.
	* @param int $debug Set as 1 to display query without executing
	* @return int TRUE on success, FALSE on failure
	*/
	function mysql_update($data, $table, $condition,$debug="0") {
		$sql = "update $table set";

		if (is_array($data)) {
			foreach ($data as $field => $value) {
				if (!is_numeric($value)) {
					$value = "'".addslashes($value)."'";
				}

				$query[] = "$field=".$value;
			}

			$query = implode(",",$query);

			$sql = trim($sql)." ".trim($query)." ".trim($condition);
		} else {
			$debug     = 1;
			$condition = 1;
		}

		if ($debug <= 0 and $condition) {
			$success = mysql_query($sql);
		} else {
			echo "<pre>$sql</pre>";
		}

		return($success);
	}


	/**
	* Returns an array list
	*
	* Takes given query and returns a standard array of all matching records.
	* The second parameter is optional and can be the name of a field you want to be the key of array cells. Should be unique!
	* If the specified field is not found, the list is returned as a regular array.
	* @param string $query MySQL query
	* @param string $key Name of field to use as array key
	* @return array Array of matching records
	*/
	function mysql_query_list($query,$key=FALSE,$grouped=false) {
		$list = array();
		$result=mysql_query($query);
		for ($a=0; $myrow=mysql_fetch_assoc($result); $a++) {
			$fields = count($myrow);

			if ($key and array_key_exists($key,$myrow)) {
				$m_key = $myrow[$key];
				if ($fields == 2) {
					unset($myrow[$key]);

					$list[$m_key] = current($myrow);
				} else {
					if (!$grouped) {
						$list[$m_key][] = $myrow;
					} else {
						$list[$m_key] = $myrow;
					}
				}
			} else {
				if ($fields <= 1) {
					$list[] = current($myrow);
				} else {
					$list[] = $myrow;
				}
			}
		}

		return($list);
	}

	/**
	* Escapes all values for MySQL use
	*
	* Takes any size array and makes each value MySQL-safe by escaping
	* all special characters as needed.
	*
	* For non-arrays, use the regular mysql_real_escape_string() function.
	* @param string $array Any size array with values that will be put in the database
	* @return array Same size array with escaped values
	*/
	function mysql_safe_array($array) {
		$array = array_each($array, mysql_real_escape_string);

        return($array);
	}

/******************************************
 * File system
 ******************************************/

	/**
	* Appends array to CSV file
	*
	* Takes specified array and appends it to a CSV file. Each array value is seen as one column value.
	* All values are escaped with double quotes.
	* Checks for valid directory location before writing.
	* @param array $data Array of data to write
	* @param string $filepath Path to write CSV file
	* @param int $maxsize Maximum file size. If this size is exceeded, the file will be deleted.
	* @return int TRUE on success, FALSE on failure
	*/
	function file_csvlog($data,$filepath,$maxsize="0") {
		$logdir = dirname($filepath);

		if (is_dir($logdir)) {
			if (file_exists($filepath)) {
				if ((filesize($filepath) > $maxsize) and ($maxsize > 0)) {
					unlink($filepath);
				}
			}

			foreach ($data as $item) {
				$line[] = "\"".$item."\"";
			}

			$line    = implode(",",$line)."\r\n";
			$success = error_log($line,3,$filepath);
		}

		return($success);
	}

	/**
	* Reads a CSV file into an array
	*
	* Reads a standard CSV file and returns an array.
	* Array structure contains a node for each line, then an array for each column value.
	* Accounting for a header row uses header labels as keys in returned array. Header row is not returned in array.
	* @param string $filepath Path to CSV file
	* @param string $headerRow Set to 1 to account for and use header row.
	* @param int $maxsize Maximum file size. If this size is exceeded, the file will be deleted.
	* @return array Array of CSV file
	*/
	function file_readcsv($filepath,$headerRow="0") {
		$array  = array();

		if (file_exists($filepath)) {
			$row    = 0;
			$handle = fopen($filepath, "r");
			while (($data = fgetcsv($handle, 1024, ",")) !== FALSE) {
			   $size = count($data);

				if ($row <= 0 and $headerRow > 0) {
					$header = $data;
			   	}

				if (is_array($header)) {
					for ($a=0; $a < $size; $a++) {
						$array[$row][$header[$a]] = $data[$a];
			    	}
				} else {
					for ($a=0; $a < $size; $a++) {
						$array[$row][] = $data[$a];
			    	}
			   }

			   $row++;
			}
			fclose($handle);

			if (is_array($header)) {
				array_shift($array);
			}
		}

 		return($array);
	}

	// Alias call to file_readcsv()
	function file_parsecsv($filepath,$headerRow="0") {
		$array = file_readcsv($filepath,$headerRow);

		return($array);
	}

	/**
	* Parses a file by line
	*
	* Reads a file and returns an array. One node per line.
	* This is an alternate for the file() function. Checks for existing file before read.
	* @param string $filepath Path to file
	* @return array Array of file line-by-line
	*/
	function file_lineparse($filepath) {
		$array = array();

		if (file_exists($filepath)) {
			$source = file($filepath);

			foreach ($source as $line) {
				$array[] = trim($line);
			}
		}

		return($array);
	}

	/**
	* Reads a file into a string
	*
	* Reads a file or URL into a string.
	* This is an alternate for the file_get_contents() function. Checks for existing file before read.
	* @param string $filepath Path to file
	* @return string Contents of file
	*/
	function file_read($filepath) {
		if (file_exists($filepath)) {
			$string = file_get_contents($filepath);
		}

		return($string);
	}

	/**
	* Writes a string to a file
	*
	* Writes a string to a new file. Will overwrite file if it already exists.
	* Checks for a valid directory file writing file.
	* @param string $filepath Path to file
	* @return string Contents of file
	*/
	function file_write($string,$filepath) {
		$logdir = dirname($filepath);

		if (is_dir($logdir)) {
			$file=fopen($filepath,"w");
			fputs($file, $string);
			fclose($file);
		}

		return($file);
	}

	// Prepend XML header to string and write to a file
	function file_writexml($string, $filepath) {
		$string = "<?xml version=\"1.0\" ?>\r\n".$string;
		$file = file_write($string, $filepath);

		return($file);
	}
	
	// Append to a file, with size limit
	function file_append($data,$filepath,$maxsize="0") {

		if (file_exists($filepath)) {
			if ((filesize($filepath) > $maxsize) and ($maxsize > 0)) {
				unlink($filepath);
			}
		}

		$success = error_log($data."\n",3,$filepath);

		return($success);
	}	

/******************************************
 * Date/Time
 ******************************************/

	/**
	* Formats date as standard MySQL-friend
	*/
	function date_formatX($date, $format="Y-m-d") {
		$date = date($format, strtotime($date));

		return($date);
	}	
	
	function date_standard($date, $format="m/d/Y") {
		$date = date($format, strtotime($date));

		return($date);
	}

	/**
	* Calculates a new date
	*
	* Takes a standard formatted date (YYYY-MM-DD) and adds a value to calculate a new date.
	* @param date $date Standard formatted date
	* @param int $int Number of days to add to the date, default is zero
	* @return date New date in standard format
	*/
	function date_calc($date, $int=0) {
		$date       = date("Y-m-d",strtotime($date));
		$this_month = date("m",strtotime($date));
		$this_day   = date("d",strtotime($date));
		$this_year  = date("Y",strtotime($date));

		$date = date("Y-m-d", mktime(0,0,0,$this_month,($this_day+$int),$this_year));

		return($date);
	}

	/**
	* Gets dates of current week
	*
	* Takes a standard formatted date (YYYY-MM-DD) and returns a standard array containing the 7 days that make up that week.
	* You can optionally select what day is used as the start of the week.
	* NOTE: Requires date_calc() function
	* @param date $date Standard formatted date
	* @param string $weekstart Name of the day that should be used as start of the week. Default is "Sunday"
	* @return array Standard array containing 7 dates
	*/
	function date_getweek($date, $weekstart="Sunday", $days=7) {
		$weekstart  = ucfirst($weekstart);
		$currentday = date("l",strtotime($date));
		$daterange  = array();
		$mathdate   = $date;

		if ($currentday != $weekstart) {
			while ($dayname != $weekstart) {
				$mathdate    = date_calc($mathdate,-1);
				$dayname     = date("l",strtotime($mathdate));
			}
		}

		for ($a=0; $a < $days; $a++) {
			$daterange[] = date_calc($mathdate,$a);
		}

		return($daterange);
	}
	
	
	/**
	* Gets different of two dates
	*
	* Takes two standard formatted date (YYYY-MM-DD) and returns the differen between the two as specified
	* by the second parameter
	* @param date $then Standard formatted date
	* @param date $now Standard formatted date	
	* @param string $count Count to return. y|m|d [d]
	*/	
	function date_change($then, $now, $count="d") {
		$diff = abs(strtotime($then) - strtotime($now));

		$years  = floor($diff / (365*60*60*24));
		$months = floor(($diff - $years * 365*60*60*24) / (30*60*60*24));
		$days   = floor(($diff - $years * 365*60*60*24 - $months*30*60*60*24)/ (60*60*24));
	
		switch ($count) {
			case "y": return($years); break;
			case "m": return($months); break;
			default:
			case "d":
				return($days); 
				break;
								
		}
	}


/******************************************
 * URLs
 ******************************************/

	/**
	* Generates new URL query
	*
	* Combines the current URL with passed variables to generate a new URL with new GET arguements.
	* @param array $replace Array with GET var name as key and value as value
	* @param array $exclude Array with GET var name as value. Matching GET vars will not be included in new URL
	* @return string Complete URL GET arguements from the ? on (including ?) - Ready for file append
	*/
	function url_build_query($replace=array(), $exclude=array(), $noget=0) {
		$list     = array();
		$query    = array();

		// Build array of values from GET
		if ($noget <= 0) {
			foreach ($_GET as $var => $value) {
				$list[$var] = $value;
			}
		}

		// Build array of values from passed
		foreach ($replace as $var => $value) {
			$list[$var] = $value;
		}

		// Remove any excluded values
		foreach ($list as $key => $value) {
			if (in_array($key, $exclude)) {
				unset($list[$key]);
			}
		}

		// Put variables into query string form
		foreach ($list as $var => $value) {
			$query[] = $var."=".urlencode($value);
		}

		$query = implode("&",$query);

		return($query);
	}

/******************************************
 * Mail
 ******************************************/

	/**
	* Sends e-mail to a specified recipient.
	*
	* Sends e-mail to the specified recipient using the mail() server function. Validtes recipient e-mail address.
	* Creates the e-mail header based on passed valuse.
	* @param string $sendto The recipient's e-mail address
	* @param string $fromname Friendly name of the sender (appears in From: header)
	* @param string $fromMail Sender's e-mail address (appears in From: header)
	* @param string $subject Subject of the e-mail
	* @param string $message Main body of the e-mail message
	* @return null
	*/
	function mail_send($recipient,$fromname,$fromMail,$subject,$message) {
		$regex = "/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-z0-9]{2,6}$/";
		if (preg_match($regex, $recipient)) {
			$mailheaders  = "From: $fromname <$fromMail>\n";
			$subject      = stripslashes($subject);
			$message      = stripslashes($message);

			$success = mail($recipient,$subject,$message,$mailheaders);
/*
			if ($success) {
				echo "<!-- Mail sent to $recipient - ".date("r")."-->\n";
			}
*/			
		}

		return($success);
	}
	
	function mail_isValidAddress($mail) {
		$regex = "/^[A-z0-9][\w.-]*@[A-z0-9][\w\-\.]+\.[A-z0-9]{2,6}$/";
		if (preg_match($regex, $mail)) { return true; } else { return false; }
	}



/******************************************
 * Strings and variables
 ******************************************/

	/**
	* Generates a random string of characters
	*
	* Generates a string of a specified length of random alphanumeric characters, or of a specified range of characters
	* @param string $charset Characters to consider for randomization, default is all alphanumerics
	* @param int $length Size of the string to return
	* @return string Random string of characters, will not include any special characters
	*/
	function str_randomchar($length=15, $charset="all") {
		if ($charset=="all") {
			$charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		}

		$charsize = strlen($charset)-1;

		$randstring = "";
		for ($a=0; $a < $length; $a++) {
			$randpos     = rand(0,$charsize);
			$randstring .= substr($charset,$randpos,1);
		}

		return($randstring);
	}

	/**
	* Makes text display-safe
	*
	* Removes invalid HTML tags and extra whitespace/breaks
	* @param string $text Text to clean
	* @return string Cleaned text
	*/
	function str_clean($string) {
		$string = trim($string);
		$string = strip_tags($string,"<p><i><em><b><strong><u><a>"); // Remove all tags except shown
		$string = nl2br($string);
		//$string = stripslashes($string);


		return($string);
	}

	// Turn patterns into regular HTML, including line breaks
	function str_tohtml($string) {
		$string = str_tolink($string);
		$string = str_tomail($string);

		return($string);
	}
	
	// Add http:// to front of string if not present
	function str_toHttp($string) {
		if (!eregi("http",$string)) {
			$string = "http://".$string;
		}

		return($string);
	}
	

	// Converts full path URLs into hyperlinks
	function str_tolink($string) {
		$string = ereg_replace('http://[a-zA-z0-9\.\,\~\/\_\?\&-\=\:]*', '<a href="\\0">\\0</a>', $string);
		$string = ereg_replace('https://[a-zA-z0-9\.\,\~\/\_\?\&-\=\:]*', '<a href="\\0">\\0</a>', $string);
		$string = ereg_replace('file://[a-zA-z0-9\.\,\~\/\_\?\&-\=\:]*', '<a href="\\0">\\0</a>', $string);
		$string = ereg_replace('ftp://[a-zA-z0-9\.\,\~\/\_\?\&-\=\:]*', '<a href="\\0">\\0</a>', $string);

		return($string);
	}

	// Converts e-mail addresses into mailto hyperlinks
	function str_tomail($string) {
		$string = ereg_replace('[_a-zA-z0-9\-]+(\.[_a-zA-z0-9\-]+)*\@' . '[_a-zA-z0-9\-]+(\.[a-zA-z]{1,3})+', '<a href="mailto:\\0">\\0</a>', $string);
		return($string);
	}

	// Escape special characters in XML element values
	function str_cleanxml($string) {
		$string = htmlspecialchars(htmlentities($string));

		return($string);
	}

	// Returns the first 40 words of a string. Maximum of 55 words returned.
	function str_summary($text, $max=40, $morelink=FALSE) {
		$text  = ereg_replace("[\r\n]"," ",$text);
		$split = explode(" ",$text);

		$count   = 0;
		$summary = array();
		foreach ($split as $word) {
			if ($word != " ") {
				$word = trim($word);
				$size = strlen($word)-1;

				$lastchar  = substr($word,$size,1);
				$summary[] = $word;

				if (ereg("[\.\!\?]",$lastchar) and ($count >= $max)) {
					break;
				}

				$count++;

				if ($count >= ($max+15)) { break; }
			} else {
				$summary[] = "\r\n";
			}
		}

		if ($count >= $max) {
			if ($morelink) {
				$summary[] = "<em><a href=\"".$morelink."\">...More</a></em>";
			} else {
				$summary[] = "<em>...More</em>";
			}
		}

		$text = implode(" ",$summary);

		return($text);
	}

	// Return variable name
	function varname(&$var, $scope=false, $prefix='unique', $suffix='value') {
		if ($scope) {
			$vals = $scope;
		} else {
			$vals = $GLOBALS;
		}

   		$old = $var;
   		$var = $new = $prefix.rand().$suffix;
   		$vname = FALSE;

   		foreach($vals as $key => $val) {
     		if($val === $new) {
     			$vname = "$".$key;
     		}
     	}

		$var = $old;

   		return($vname);
	}

	// Get keywords from passed string
	// Optional file of stop words for comparing
	function str_keywords($phrase, $stopfile=FALSE) {
		$stopwords   = array();
		$keywords    = array();
		$keywordlist = explode(" ",$phrase);

		if ($stopfile == TRUE) {
			$wordlist  = file_read($stopfile);
			$wordlist  = strtolower($wordlist);
			$stopwords = explode("\r\n",$wordlist);
		}

		foreach ($keywordlist as $word) {
			if (!in_array(strtolower($word),$stopwords)) {
				$keywords[] = $word;
			}
		}

		return($keywords);
	}


	function verbose($string) {
		global $verbose;

		if ($verbose == TRUE) {
			echo "<pre>$string</pre>\n";
		}

		return;
	}

/******************************************
 * Integers/Math
 ******************************************/

	// Returns proper ordinal suffix for any number
	function int_ordinal($number) {
	    if ($number % 100 > 10 && $number %100 < 14) {
	        $suffix = "th";
	    } else {
	        switch($number % 10) {

	            case 0:
	                $suffix = "th";
	                break;

	            case 1:
	                $suffix = "st";
	                break;

	            case 2:
	                $suffix = "nd";
	                break;

	            case 3:
	                $suffix = "rd";
	                break;

	            default:
	                $suffix = "th";
	                break;
	        }
	    }

	    return($number.$suffix);
	}

/******************************************
 * HTML
 ******************************************/

	// Create regular HTML unordered list
	function html_list($array, $ulclass="", $liclass="") {
		if (is_array($array)) {
			if ($ulclass) {
				$firstchar = substr($ulclass,0,1);

				if ($firstchar == "#") {
					$ulclass = " id=\"".$ulclass."\"";
				} else {
					$ulclass = " class=\"".$ulclass."\"";
				}
			}

			if ($liclass) {
				$liclass = " class=\"".$liclass."\"";
			}

			echo "<ul$ulclass>\n";

			foreach ($array as $label => $url) {
				if (!is_numeric($label)) {
					$free   = explode("|",$url);

					if ($free[1]) {
						$target = " target=\"".$free[1]."\"";
						$url    = $free[0];
					} else {
						unset($target);
					}

					echo "<li$liclass><a href=\"".$url."\"$target>".$label."</a></li>\r\n";
				} else {
					echo "<li$liclass>".$url."</li>\r\n";
				}
			}
		?>
				</ul>
		<?
		}

		return;
	}

	// Take array and output as dropdown menu
	function html_form_dropdown($array, $selected="null", $name="null", $reverse=FALSE) {
		if (is_array($array)) {
			foreach ($array as $label => $value) {
				if ($reverse == TRUE) {
					$save = $value;
					$value = $label;
					$label = $save;
				}

				if (($value == $selected) and ($selected != "null")) {
					$thisone = " selected";
				} else {
					unset($thisone);
				}

				$list .= "<option value=\"".$value."\"$thisone>".$label."</option>\r\n";
			}

			if ($name != "null" and $name != "") {
				?>
				<select name="<? echo $name; ?>" id="<?= $name; ?>"><? echo $list; ?></select>
				<?
			} else {
				echo $list;
			}
		}

		return;
	}


	// Look for 'checked' values from array
	// !! Reference variable alters existing array !!
	function html_form_getchecked(&$array, $field, $match, $thisone="checked") {
		if (is_array($array)) {
			if (array_key_exists($field, $array)) {
				if ($array[$field] == $match) {
					$array[$field] = $thisone;
				}
			}
		}

		return($array);
	}

	// Check alternate row coloring
	function html_rowcolor($color, $class="altrow") {
		if ($color == $class) {
			$color = "";
		} else {
			$color = $class;
		}

		return($color);
	}

	// Make all GET variables hidden form variables
	function html_form_hiddenfields() {
		$args = func_get_args();

		foreach ($_GET as $var => $value) {
			if (!in_array($var,$args)) {
				echo "<input type=\"hidden\" name=\"".$var."\" value=\"".$value."\">\n";
			}
		}

		return;
	}

/******************************************
 * FTP
 ******************************************/
	function ftp($host, $username, $password) {
		$connect = ftp_connect($host);
		$success = ftp_login($connect, $username, $password);

		if ($success) {
			return($connect);
		} else {
			echo "<pre>FTP Attempt failed</pre>";
			return;
		}
	}

/******************************************
 * Image manipulations (GD library)
 ******************************************/

/******************************************
 * Call functions
 * Brian Vaughn, 11/18/2004
 ******************************************/

	/**
	* Get thumbnail dimensions for an image
	*
	* Takes the original dimensions of the named image file and calculates the correct proportions for the resized image.
	* Use this function for thumbnails and force fitting images that may be too large.
	* Note: This function does not create image files. Use it to get dimensions to resize an image use HTML.
	*
	* If you provide a maximum height greather than zero, the function continue to calculate dimensions until it is below the maximum height.
	* This means the final width may be smaller than the maximum width desired.
	*
	* Returned array has the following keys and values:
	* - height, integer of height
	* - width, integer of width
	* @param string $imgpath Absolute path to the image file
	* @param int $maxwidth Maximum width of the resized dimensions
	* @param int $maxheight Maximum height of the resized dimensions
	* @return array Associative array contain the dimensions. See comments for structure.
	*/
	function image_resizedimensions($imgpath,$maxwidth="125",$maxheight="0") {
		$imagehw     = image_getsize($imgpath);
		$imagewidth  = $imagehw["width"];
		$imageheight = $imagehw["height"];
		$imgorig     = $imagewidth;

		if ($imagewidth > $maxwidth) {
			$imageprop   = ($maxwidth*100)/$imagewidth;
			$imagevsize  = ($imageheight*$imageprop)/100;
			$imagewidth  = $maxwidth;
			$imageheight = ceil($imagevsize);
		}

		while (($imageheight > $maxheight) and ($maxheight > 0)) {
			$maxwidth = $maxwidth - 5;

			if ($imagewidth > $maxwidth) {
				$imageprop   = ($maxwidth*100)/$imagewidth;
				$imagevsize  = ($imageheight*$imageprop)/100;
				$imagewidth  = $maxwidth;
				$imageheight = ceil($imagevsize);
			}
		}

		$thumbnail = array(
							"height" => $imageheight,
							"width"  => $imagewidth
						);
		return($thumbnail);
	}

	// Resize an image and display
	function image_resizeX($path, $saveas=FALSE, $newwidth, $newheight="0") {
		$filename = basename($path);
		$filepath = dirname($path);

		$newsize = image_resizedimensions($filepath."/".$filename,$newwidth,$newheight);

		$gd = new ImageEditor($filename, $filepath);
		$gd->resize($newsize["width"], $newsize["height"]);

		if ($saveas) {
			$savename = basename($saveas);
			$savepath = dirname($saveas)."/";

			$gd->outputFile($savename, $savepath);
		} else {
			$gd->outputImage();
		}

		return;
	}


	/**
	* Get image dimensions
	*
	* Returns an array of image dimensions in pixels. This is a alternate call for GetImageSize()
	* This function checks for existing file before attempting native function call.
	*
	* Returned array has the following keys and values:
	* - height, integer of height
	* - width, integer of width
	* @param string $path Absolute path to the image file
	* @return array Associative array contain the dimensions. See comments for structure.
	*/
	function image_getsize($path) {
		$imgsize = array();

		if (file_exists($path)) {
			$imagehw           = GetImageSize($path);
			$imgsize["width"]  = $imagehw[0];
			$imgsize["height"] = $imagehw[1];
		}

		return($imgsize);
	}

/******************************************
 *  Script Info
 *  ===========
 *  File: ImageEditor.php
 *  Created: 05/06/03
 *  Modified: 05/06/03
 *  Author: Ash Young (ash@evoluted.net
 *  Website: http: * evoluted.net/php/image-editor.htm
 *  Requirements: PHP with the GD Library
 * Description
 *  ===========
 *  This class allows you to edit an image easily and
 *  quickly via php.
 * If you have any functions that you like to see
 *  implemented in this script then please just send
 *  an email to ash@evoluted.net
 * Limitations
 *  ===========
 *  - GIF Editing: this script will only edit gif files
 *      your GD library allows this.
 * Image Editing Functions
 *  =======================
 *  resize(int width, int height)
 *     resizes the image to proportions specified.
 * crop(int x, int y, int width, int height)
 *     crops the image starting at (x, y) into a rectangle
 *     width wide and height high.
 * addText(String str, int x, int y, Array color)
 *     adds the string str to the image at position (x, y)
 *     using the colour given in the Array color which
 *     represents colour in RGB mode.
 * addLine(int x1, int y1, int x2, int y2, Array color)
 *     adds the line starting at (x1,y1) ending at (x2,y2)
 *     using the colour given in the Array color which
 *     represents colour in RGB mode.
 * Useage
 *  ======
 *  First you are required to include this file into your
 *  php script and then to create a new instance of the
 *  class, giving it the path and the filename of the
 *  image that you wish to edit. Like so:
 * include("ImageEditor.php");
 *  $imageEditor = new ImageEditor("filename.jpg", "directoryfileisin/");
 * After you have done this you will be able to edit the
 *  image easily and quickly. You do this by calling a
 *  function to act upon the image. See below for function
 *  definitions and descriptions see above. An example
 *  would be:
 * $imageEditor->resize(400, 300);
 * This would resize our imported image to 400 pixels by
 *  300 pixels. To then export the edited image there are
 *  two choices, out put to file and to display as an image.
 *  If you are displaying as an image however it is assumed
 *  that this file will be viewed as an image rather than
 *  as a webpage. The first line below saves to file, the
 *  second displays the image.
 * $imageEditor->outputFile("filenametosaveto.jpg", "directorytosavein/");
 * $imageEditor->outputImage();
/****************************************************************/

class ImageEditor {
  var $x;
  var $y;
  var $type;
  var $img;
  var $error;

  /****************************************************************/
  // CONSTRUCTOR
  /****************************************************************/
  function ImageEditor($filename, $path, $col=NULL)
  {
    $this->error = false;
    if(is_numeric($filename) && is_numeric($path))
    // IF NO IMAGE SPECIFIED CREATE BLANK IMAGE
    {
      $this->x = $filename;
      $this->y = $path;
      $this->type = "jpg";
      $this->img = imagecreatetruecolor($this->x, $this->y);
      if(is_array($col))
      // SET BACKGROUND COLOUR OF IMAGE
      {
        $colour = ImageColorAllocate($this->img, $col[0], $col[1], $col[2]);
        ImageFill($this->img, 0, 0, $colour);
      }
    }
    else
    // IMAGE SPECIFIED SO LOAD THIS IMAGE
    {
      // FIRST SEE IF WE CAN FIND IMAGE

      if(file_exists($path . $filename))
      {
        $file = $path . $filename;
      }
      else if (file_exists($path . "/" . $filename))
      {
        $file = $path . "/" . $filename;
      }
      else
      {
        $this->errorImage("File Could Not Be Loaded");
      }

      if(!($this->error))
      {
        // LOAD OUR IMAGE WITH CORRECT FUNCTION
        $this->type = strtolower(end(explode('.', $filename)));
        if ($this->type == 'jpg' || $this->type == 'jpeg')
        {
          $this->img = @imagecreatefromjpeg($file);
        }
        else if ($this->type == 'png')
        {
          $this->img = @imagecreatefrompng($file);
        }
        else if ($this->type == 'gif')
        {
          $this->img = @imagecreatefrompng($file);
        }
        // SET OUR IMAGE VARIABLES
        $this->x = imagesx($this->img);
        $this->y = imagesy($this->img);
      }
    }
  }

  /****************************************************************/
  // RESIZE IMAGE GIVEN X AND Y
  /****************************************************************/
  function resize($width, $height)
  {
    if(!$this->error)
    {
      $tmpimage = imagecreatetruecolor($width, $height);
      imagecopyresampled($tmpimage, $this->img, 0, 0, 0, 0,
                           $width, $height, $this->x, $this->y);
      imagedestroy($this->img);
      $this->img = $tmpimage;
      $this->y = $height;
      $this->x = $width;
    }
  }

  /****************************************************************/
  // CROPS THE IMAGE, GIVE A START CO-ORDINATE AND
  // LENGTH AND HEIGHT ATTRIBUTES
  /****************************************************************/
  function crop($x, $y, $width, $height)
  {
    if(!$this->error)
    {
      $tmpimage = imagecreatetruecolor($width, $height);
      imagecopyresampled($tmpimage, $this->img, 0, 0, $x, $y,
                           $width, $height, $width, $height);
      imagedestroy($this->img);
      $this->img = $tmpimage;
      $this->y = $height;
      $this->x = $width;
    }
  }

  /****************************************************************/
  // ADDS TEXT TO AN IMAGE, TAKES THE STRING, A STARTING
  // POINT, PLUS A COLOR DEFINITION AS AN ARRAY IN RGB MODE
  /****************************************************************/
  function addText($str, $x, $y, $col)
  {
    if(!$this->error)
    {
      $colour = ImageColorAllocate($this->img, $col[0], $col[1], $col[2]);
      Imagestring($this->img, 5, $x, $y, $str, $colour);
    }
  }

  /****************************************************************/
  // ADDS A LINE TO AN IMAGE, TAKES A STARTING AND AN END
  // POINT, PLUS A COLOR DEFINITION AS AN ARRAY IN RGB MODE
  /****************************************************************/
  function addLine($x1, $y1, $x2, $y2, $col)
  {
    if(!$this->error)
    {
      $colour = ImageColorAllocate($this->img, $col[0], $col[1], $col[2]);
      ImageLine($this->img, $x1, $y1, $x2, $y2, $colour);
    }
  }

  /****************************************************************/
  // RETURN OUR EDITED FILE AS AN IMAGE
  /****************************************************************/
  function outputImage()
  {
    if ($this->type == 'jpg' || $this->type == 'jpeg')
    {
      header("Content-type: image/jpeg");
      imagejpeg($this->img);
    }
    else if ($this->type == 'png')
    {
      header("Content-type: image/png");
      imagepng($this->img);
    }
    else if ($this->type == 'gif')
    {
      header("Content-type: image/png");
      imagegif($this->img);
    }
  }

  /****************************************************************/
  // CREATE OUR EDITED FILE ON THE SERVER
  /****************************************************************/
  function outputFile($filename, $path)
  {
    if ($this->type == 'jpg' || $this->type == 'jpeg')
    {
      imagejpeg($this->img, ($path . $filename));
    }
    else if ($this->type == 'png')
    {
      imagepng($this->img, ($path . $filename));
    }
    else if ($this->type == 'gif')
    {
      imagegif($this->img, ($path . $filename));
    }
  }


  /****************************************************************/
  // SET OUTPUT TYPE IN ORDER TO SAVE IN DIFFERENT
  // TYPE THAN WE LOADED
  /****************************************************************/
  function setImageType($type)
  {
    $this->type = $type;
  }

  /****************************************************************/
  // GET VARIABLE FUNCTIONS
  /****************************************************************/
  function getWidth()                {return $this->x;}
  function getHeight()               {return $this->y;}
  function getImageType()            {return $this->type;}

  /****************************************************************/
  // CREATES AN ERROR IMAGE SO A PROPER OBJECT IS RETURNED
  /****************************************************************/
  function errorImage($str)
  {
    $this->error = false;
    $this->x = 235;
    $this->y = 50;
    $this->type = "jpg";
    $this->img = imagecreatetruecolor($this->x, $this->y);
    $this->addText("AN ERROR OCCURED:", 10, 5, array(250,70,0));
    $this->addText($str, 10, 30, array(255,255,255));
    $this->error = true;
  }
}


/******************************************
 * XML
 ******************************************/

/******************************************
 * Call functions
 * Brian Vaughn, 11/18/2004
 ******************************************/

	// Parses local XML file or XML URL - Proper form required
	function xml_parsefile($filepath, $filesize=250000) {
		$hosts = array("http","https");
		foreach ($hosts as $find) {
			if (eregi($find,$filepath)) {
				$url = $filepath;
				break;
			}
		}

		// If passed is not URL, read local file
		if (file_exists($filepath) and !$url) {
			$file = fopen($filepath, "r");
			$xmlsource = fread($file, filesize($filepath));
			fclose($file);
		} else {
			// Read URL as XML file (<=250kb)
			if ($url) {
				$file = fopen($filepath, "r");
				$xmlsource = fread($file, $filesize);
				fclose($file);
			} else {
				// Take direct variable and parse
				$xmlsource = $filepath;
			}
		}

		$xmlarray = XML_unserialize($xmlsource);

		return($xmlarray);
	}

	// Alias for function xml_parsefile() {
	function xml_parser($filepath, $filesize=250000) {
		$array = xml_parsefile($filepath, $filesize);
		return($array);
	}

	// Turns array into ready-to-write XML string
	function array_createxml($array) {
		$result = XML_serialize($array);

		return($result);
	}

/******************************************
 * An XML-RPC implementation by Keith Devens, version 2.5e.
 * http://www.keithdevens.com/software/xmlrpc/
 *
 * Release history available at:
 * http://www.keithdevens.com/software/xmlrpc/history/
 *
 * This code is Open Source, released under terms similar to the Artistic License.
 * Read the license at http://www.keithdevens.com/software/license/
 *
 * Note: this code requires version 4.1.0 or higher of PHP.
 ******************************************/

/* XML-RPC */
function & XML_serialize(&$data, $level = 0, $prior_key = NULL){
	//assumes a hash, keys are the variable names
	$xml_serialized_string = "";
	while(list($key, $value) = each($data)){
		$inline = false;
		$numeric_array = false;
		$attributes = "";
		//echo "My current key is '$key', called with prior key '$prior_key'<br>";
		if(!strstr($key, "_attributes")){ //if it's not an attribute
			if(array_key_exists($key."_attributes", $data)){
				while(list($attr_name, $attr_value) = each($data[$key."_attributes"])){
					//echo "Found attribute $attribute_name with value $attribute_value<br>";
					$attr_value = &htmlspecialchars($attr_value, ENT_QUOTES);
					$attributes .= " $attr_name=\"$attr_value\"";
				}
			}

			if(is_numeric($key)){
				//echo "My current key ($key) is numeric. My parent key is '$prior_key'<br>";
				$key = $prior_key;
			}else{
				//you can't have numeric keys at two levels in a row, so this is ok
				//echo "Checking to see if a numeric key exists in data.";
				if(is_array($value) and array_key_exists(0, $value)){
				//	echo " It does! Calling myself as a result of a numeric array.<br>";
					$numeric_array = true;
					$xml_serialized_string .= XML_serialize($value, $level, $key);
				}
				//echo "<br>";
			}

			if(!$numeric_array){
				$xml_serialized_string .= str_repeat("\t", $level) . "<$key$attributes>";

				if(is_array($value)){
					$xml_serialized_string .= "\r\n" . XML_serialize($value, $level+1);
				}else{
					$inline = true;
					$xml_serialized_string .= $value;
				}

				$xml_serialized_string .= (!$inline ? str_repeat("\t", $level) : "") . "</$key>\r\n";
			}
		}else{
			//echo "Skipping attribute record for key $key<bR>";
		}
	}

	/*
	if($level == 0) {
		$xml_serialized_string = "<?xml version=\"1.0\" ?>\r\n" . $xml_serialized_string;
		return($xml_serialized_string);
	} else {
		return($xml_serialized_string);
	}
	*/

	return($xml_serialized_string);
}

class XML {
	var $parser; //a reference to the XML parser
	var $document; //the entire XML structure built up so far
	var $current; //a pointer to the current item - what is this
	var $parent; //a pointer to the current parent - the parent will be an array
	var $parents; //an array of the most recent parent at each level

	var $last_opened_tag;

	function XML($data=null){
		$this->parser = xml_parser_create();

		xml_parser_set_option ($this->parser, XML_OPTION_CASE_FOLDING, 0);
		xml_set_object($this->parser, &$this);
		xml_set_element_handler($this->parser, "open", "close");
		xml_set_character_data_handler($this->parser, "data");
//		register_shutdown_function(array(&$this, 'destruct'));
	}

	function destruct(){
		xml_parser_free($this->parser);
	}

	function parse($data){
		$this->document = array();
		$this->parent = &$this->document;
		$this->parents = array();
		$this->last_opened_tag = NULL;
		xml_parse($this->parser, $data);
		return $this->document;
	}

	function open($parser, $tag, $attributes){
		//echo "Opening tag $tag<br>\n";
		$this->data = "";
		$this->last_opened_tag = $tag; //tag is a string
		if(array_key_exists($tag, $this->parent)){
			//echo "There's already an instance of '$tag' at the current level ($level)<br>\n";
			if(is_array($this->parent[$tag]) and array_key_exists(0, $this->parent[$tag])){ //if the keys are numeric
				//need to make sure they're numeric (account for attributes)
				$key = count_numeric_items($this->parent[$tag]);
				//echo "There are $key instances: the keys are numeric.<br>\n";
			}else{
				//echo "There is only one instance. Shifting everything around<br>\n";
				$temp = &$this->parent[$tag];
				unset($this->parent[$tag]);
				$this->parent[$tag][0] = &$temp;

				if(array_key_exists($tag."_attributes", $this->parent)){
					//shift the attributes around too if they exist
					$temp = &$this->parent[$tag."_attributes"];
					unset($this->parent[$tag."_attributes"]);
					$this->parent[$tag]["0_attributes"] = &$temp;
				}
				$key = 1;
			}
			$this->parent = &$this->parent[$tag];
		}else{
			$key = $tag;
		}
		if($attributes){
			$this->parent[$key."_attributes"] = $attributes;
		}

		$this->parent[$key] = array();
		$this->parent = &$this->parent[$key];
		array_unshift($this->parents, &$this->parent);
	}

	function data($parser, $data){
		//echo "Data is '", htmlspecialchars($data), "'<br>\n";
		if($this->last_opened_tag != NULL){
			$this->data .= $data;
		}
	}

	function close($parser, $tag){
		//echo "Close tag $tag<br>\n";
		if($this->last_opened_tag == $tag){
			$this->parent = $this->data;
			$this->last_opened_tag = NULL;
		}
		array_shift($this->parents);
		$this->parent = &$this->parents[0];
	}
}

function & XML_unserialize(&$xml){
	$xml_parser = new XML();
	$data = &$xml_parser->parse(&$xml);
	$xml_parser->destruct();
	return $data;
}

function & XMLRPC_parse(&$request){
	if(defined('XMLRPC_DEBUG') and XMLRPC_DEBUG){
		XMLRPC_debug('XMLRPC_parse', "<p>Received the following raw request:</p>" . XMLRPC_show($request, 'print_r', true));
	}
	$data = &XML_unserialize(&$request);
	if(defined('XMLRPC_DEBUG') and XMLRPC_DEBUG){
		XMLRPC_debug('XMLRPC_parse', "<p>Returning the following parsed request:</p>" . XMLRPC_show($data, 'print_r', true));
	}
	return $data;
}

function & XMLRPC_prepare($data, $type = NULL){
	if(is_array($data)){
		$num_elements = count($data);
		if((array_key_exists(0, $data) or !$num_elements) and $type != 'struct'){ //it's an array
			if(!$num_elements){ //if the array is empty
				$returnvalue =  array('array' => array('data' => NULL));
			}else{
				$returnvalue['array']['data']['value'] = array();
				$temp = &$returnvalue['array']['data']['value'];
				$count = count_numeric_items($data);
				for($n=0; $n<$count; $n++){
					$type = NULL;
					if(array_key_exists("$n type", $data)){
						$type = $data["$n type"];
					}
					$temp[$n] = XMLRPC_prepare(&$data[$n], $type);
				}
			}
		}else{ //it's a struct
			if(!$num_elements){ //if the struct is empty
				$returnvalue = array('struct' => NULL);
			}else{
				$returnvalue['struct']['member'] = array();
				$temp = &$returnvalue['struct']['member'];
				while(list($key, $value) = each($data)){
					if(substr($key, -5) != ' type'){ //if it's not a type specifier
						$type = NULL;
						if(array_key_exists("$key type", $data)){
							$type = $data["$key type"];
						}
						$temp[] = array('name' => $key, 'value' => XMLRPC_prepare(&$value, $type));
					}
				}
			}
		}
	}else{ //it's a scalar
		if(!$type){
			if(is_int($data)){
				$returnvalue['int'] = $data;
				return $returnvalue;
			}elseif(is_float($data)){
				$returnvalue['double'] = $data;
				return $returnvalue;
			}elseif(is_bool($data)){
				$returnvalue['boolean'] = ($data ? 1 : 0);
				return $returnvalue;
			}elseif(preg_match('/^\d{8}T\d{2}:\d{2}:\d{2}$/', $data, $matches)){ //it's a date
				$returnvalue['dateTime.iso8601'] = $data;
				return $returnvalue;
			}elseif(is_string($data)){
				$returnvalue['string'] = htmlspecialchars($data);
				return $returnvalue;
			}
		}else{
			$returnvalue[$type] = htmlspecialchars($data);
		}
	}
	return $returnvalue;
}

function & XMLRPC_adjustValue(&$current_node){
	if(is_array($current_node)){
		if(isset($current_node['array'])){
			if(!is_array($current_node['array']['data'])){
				//If there are no elements, return an empty array
				return array();
			}else{
				//echo "Getting rid of array -> data -> value<br>\n";
				$temp = &$current_node['array']['data']['value'];
				if(is_array($temp) and array_key_exists(0, $temp)){
					$count = count($temp);
					for($n=0;$n<$count;$n++){
						$temp2[$n] = &XMLRPC_adjustValue(&$temp[$n]);
					}
					$temp = &$temp2;
				}else{
					$temp2 = &XMLRPC_adjustValue(&$temp);
					$temp = array(&$temp2);
					//I do the temp assignment because it avoids copying,
					// since I can put a reference in the array
					//PHP's reference model is a bit silly, and I can't just say:
					// $temp = array(&XMLRPC_adjustValue(&$temp));
				}
			}
		}elseif(isset($current_node['struct'])){
			if(!is_array($current_node['struct'])){
				//If there are no members, return an empty array
				return array();
			}else{
				//echo "Getting rid of struct -> member<br>\n";
				$temp = &$current_node['struct']['member'];
				if(is_array($temp) and array_key_exists(0, $temp)){
					$count = count($temp);
					for($n=0;$n<$count;$n++){
						//echo "Passing name {$temp[$n][name]}. Value is: " . show($temp[$n][value], var_dump, true) . "<br>\n";
						$temp2[$temp[$n]['name']] = &XMLRPC_adjustValue(&$temp[$n]['value']);
						//echo "adjustValue(): After assigning, the value is " . show($temp2[$temp[$n]['name']], var_dump, true) . "<br>\n";
					}
				}else{
					//echo "Passing name $temp[name]<br>\n";
					$temp2[$temp['name']] = &XMLRPC_adjustValue(&$temp['value']);
				}
				$temp = &$temp2;
			}
		}else{
			$types = array('string', 'int', 'i4', 'double', 'dateTime.iso8601', 'base64', 'boolean');
			$fell_through = true;
			foreach($types as $type){
				if(array_key_exists($type, $current_node)){
					//echo "Getting rid of '$type'<br>\n";
					$temp = &$current_node[$type];
					//echo "adjustValue(): The current node is set with a type of $type<br>\n";
					$fell_through = false;
					break;
				}
			}
			if($fell_through){
				$type = 'string';
				//echo "Fell through! Type is $type<br>\n";
			}
			switch ($type){
				case 'int': case 'i4': $temp = (int)$temp;    break;
				case 'string':         $temp = (string)$temp; break;
				case 'double':         $temp = (double)$temp; break;
				case 'boolean':        $temp = (bool)$temp;   break;
			}
		}
	}else{
		$temp = (string)$current_node;
	}
	return $temp;
}

function XMLRPC_getParams($request){
	if(!is_array($request['methodCall']['params'])){
		//If there are no parameters, return an empty array
		return array();
	}else{
		//echo "Getting rid of methodCall -> params -> param<br>\n";
		$temp = &$request['methodCall']['params']['param'];
		if(is_array($temp) and array_key_exists(0, $temp)){
			$count = count($temp);
			for($n = 0; $n < $count; $n++){
				//echo "Serializing parameter $n<br>";
				$temp2[$n] = &XMLRPC_adjustValue(&$temp[$n]['value']);
			}
		}else{
			$temp2[0] = &XMLRPC_adjustValue($temp['value']);
		}
		$temp = &$temp2;
		return $temp;
	}
}

function XMLRPC_getMethodName($methodCall){
	//returns the method name
	return $methodCall['methodCall']['methodName'];
}

function XMLRPC_request($site, $location, $methodName, $params = NULL, $user_agent = NULL){
	$site = explode(':', $site);
	if(isset($site[1]) and is_numeric($site[1])){
		$port = $site[1];
	}else{
		$port = 80;
	}
	$site = $site[0];

	$data["methodCall"]["methodName"] = $methodName;
	$param_count = count($params);
	if(!$param_count){
		$data["methodCall"]["params"] = NULL;
	}else{
		for($n = 0; $n<$param_count; $n++){
			$data["methodCall"]["params"]["param"][$n]["value"] = $params[$n];
		}
	}
	$data = XML_serialize($data);

	if(defined('XMLRPC_DEBUG') and XMLRPC_DEBUG){
		XMLRPC_debug('XMLRPC_request', "<p>Received the following parameter list to send:</p>" . XMLRPC_show($params, 'print_r', true));
	}
	$conn = fsockopen ($site, $port); //open the connection
	if(!$conn){ //if the connection was not opened successfully
		if(defined('XMLRPC_DEBUG') and XMLRPC_DEBUG){
			XMLRPC_debug('XMLRPC_request', "<p>Connection failed: Couldn't make the connection to $site.</p>");
		}
		return array(false, array('faultCode'=>10532, 'faultString'=>"Connection failed: Couldn't make the connection to $site."));
	}else{
		$headers =
			"POST $location HTTP/1.0\r\n" .
			"Host: $site\r\n" .
			"Connection: close\r\n" .
			($user_agent ? "User-Agent: $user_agent\r\n" : '') .
			"Content-Type: text/xml\r\n" .
			"Content-Length: " . strlen($data) . "\r\n\r\n";

		fputs($conn, "$headers");
		fputs($conn, $data);

		if(defined('XMLRPC_DEBUG') and XMLRPC_DEBUG){
			XMLRPC_debug('XMLRPC_request', "<p>Sent the following request:</p>\n\n" . XMLRPC_show($headers . $data, 'print_r', true));
		}

		//socket_set_blocking ($conn, false);
		$response = "";
		while(!feof($conn)){
			$response .= fgets($conn, 1024);
		}
		fclose($conn);

		//strip headers off of response
		$data = XML_unserialize(substr($response, strpos($response, "\r\n\r\n")+4));

		if(defined('XMLRPC_DEBUG') and XMLRPC_DEBUG){
			XMLRPC_debug('XMLRPC_request', "<p>Received the following response:</p>\n\n" . XMLRPC_show($response, 'print_r', true) . "<p>Which was serialized into the following data:</p>\n\n" . XMLRPC_show($data, 'print_r', true));
		}
		if(isset($data['methodResponse']['fault'])){
			$return =  array(false, XMLRPC_adjustValue(&$data['methodResponse']['fault']['value']));
			if(defined('XMLRPC_DEBUG') and XMLRPC_DEBUG){
				XMLRPC_debug('XMLRPC_request', "<p>Returning:</p>\n\n" . XMLRPC_show($return, 'var_dump', true));
			}
			return $return;
		}else{
			$return = array(true, XMLRPC_adjustValue(&$data['methodResponse']['params']['param']['value']));
			if(defined('XMLRPC_DEBUG') and XMLRPC_DEBUG){
				XMLRPC_debug('XMLRPC_request', "<p>Returning:</p>\n\n" . XMLRPC_show($return, 'var_dump', true));
			}
			return $return;
		}
	}
}

function XMLRPC_response($return_value, $server = NULL){
	$data["methodResponse"]["params"]["param"]["value"] = &$return_value;
	$return = XML_serialize(&$data);

	if(defined('XMLRPC_DEBUG') and XMLRPC_DEBUG){
		XMLRPC_debug('XMLRPC_response', "<p>Received the following data to return:</p>\n\n" . XMLRPC_show($return_value, 'print_r', true));
	}

	header("Connection: close");
	header("Content-Length: " . strlen($return));
	header("Content-Type: text/xml");
	header("Date: " . date("r"));
	if($server){
		header("Server: $server");
	}

	if(defined('XMLRPC_DEBUG') and XMLRPC_DEBUG){
		XMLRPC_debug('XMLRPC_response', "<p>Sent the following response:</p>\n\n" . XMLRPC_show($return, 'print_r', true));
	}
	echo $return;
}

function XMLRPC_error($faultCode, $faultString, $server = NULL){
	$array["methodResponse"]["fault"]["value"]["struct"]["member"] = array();
	$temp = &$array["methodResponse"]["fault"]["value"]["struct"]["member"];
	$temp[0]["name"] = "faultCode";
	$temp[0]["value"]["int"] = $faultCode;
	$temp[1]["name"] = "faultString";
	$temp[1]["value"]["string"] = $faultString;

	$return = XML_serialize($array);

	header("Connection: close");
	header("Content-Length: " . strlen($return));
	header("Content-Type: text/xml");
	header("Date: " . date("r"));
	if($server){
		header("Server: $server");
	}
	if(defined('XMLRPC_DEBUG') and XMLRPC_DEBUG){
		XMLRPC_debug('XMLRPC_error', "<p>Sent the following error response:</p>\n\n" . XMLRPC_show($return, 'print_r', true));
	}
	echo $return;
}

function XMLRPC_convert_timestamp_to_iso8601($timestamp){
	//takes a unix timestamp and converts it to iso8601 required by XMLRPC
	//an example iso8601 datetime is "20010822T03:14:33"
	return date("Ymd\TH:i:s", $timestamp);
}

function XMLRPC_convert_iso8601_to_timestamp($iso8601){
	return strtotime($iso8601);
}

function count_numeric_items(&$array){
	return is_array($array) ? count(array_filter(array_keys($array), 'is_numeric')) : 0;
}

function XMLRPC_debug($function_name, $debug_message){
	$GLOBALS['XMLRPC_DEBUG_INFO'][] = array($function_name, $debug_message);
}

function XMLRPC_debug_print(){
	if($GLOBALS['XMLRPC_DEBUG_INFO']){
		echo "<table border=\"1\" width=\"100%\">\n";
		foreach($GLOBALS['XMLRPC_DEBUG_INFO'] as $debug){
			echo "<tr><th style=\"vertical-align: top\">$debug[0]</th><td>$debug[1]</td></tr>\n";
		}
		echo "</table>\n";
		unset($GLOBALS['XMLRPC_DEBUG_INFO']);
	}else{
		echo "<p>No debugging information available yet.</p>";
	}
}

function XMLRPC_show($data, $func = "print_r", $return_str = false){
	ob_start();
	$func($data);
	$output = ob_get_contents();
	ob_end_clean();
	if($return_str){
		return "<pre>" . htmlspecialchars($output) . "</pre>\n";
	}else{
		echo "<pre>", htmlspecialchars($output), "</pre>\n";
	}
}

?>