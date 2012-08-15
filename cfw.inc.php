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


?>