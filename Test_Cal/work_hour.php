<?php
// Include config file
include "config.php";

class Calendar {  
     
    /**
     * Constructor
     */
    public function __construct(){     
        $this->naviHref = htmlentities($_SERVER['PHP_SELF']);
    }
     
    /********************* PROPERTY ********************/  
    private $dayLabels = array("Mon","Tue","Wed","Thu","Fri","Sat","Sun");
     
    private $currentYear=0;
     
    private $currentDay=0;
     
    private $naviHref= null;
    private $selectedWeek = 0;
    private $displayedWeek = 0;
    private $dateToWorkedHours = [];
    private $booked_hours_err = '';
     
    /********************* PUBLIC **********************/  
        
    /**
    * print out the calendar
    */
    public function show() {       
        if(array_key_exists('button1', $_POST)) {

            foreach($_POST as $key=>$value){
                if($key == 'button1'){
                    continue;
                }
                $this->_saveHoursBooked($key,$value);
            }
        }
         
        if(null==$selectedWeek&&isset($_GET['week'])){
 
            $this->selectedWeek = $_GET['week'];
            $weekModifier = $_GET['week'] - date("W",time());
         
        }else if(null==$selectedWeek){
 
            $this->selectedWeek = date("W",time());
            $weekModifier = 0;
            $this->displayedWeek = $this->selectedWeek;
        }  


        $date = new DateTime();
    
        if($date->format('N') !== 1) {
            $date->sub(new DateInterval('P'. $date->format('N') . 'D'));
        }
    
        $interval = new DateInterval('P'.abs($weekModifier).'W');
    
        if($weekModifier > 0) {
            $date->add($interval);	
        } else {
            $date->sub($interval);	
        }
        $this->currentYear = intval($date->format('Y'));

        $this->displayedWeek = intval($date->format('W'));
        $content='<form method="post">';
        $content.='<div id="calendar">'.
                        '<div class="box">'.
                        $this->_createNavi().
                        '</div>'.
                        '<div class="box-content">'.
                                '<ul class="label">'.$this->_createLabels().'</ul>';   
                                $content.='<div class="clear"></div>';     
                                $content.='<ul class="dates">';    
                                 
        for($i = 1; $i <= 7; $i++) {
            $cellContent = $date->add(new DateInterval('P1D'))->format('d.M');
            $dbDate = $date->format('d_M_Y');
            $hours_worked = 0;
            $sql = "SELECT * FROM booking WHERE booking_date = ?";
            $link = $this->_getMySQLLink();
            if($stmt = mysqli_prepare($link, $sql)){
                // Bind variables to the prepared statement as parameters
                mysqli_stmt_bind_param($stmt, "i", $param_id);
                
                // Set parameters
                $param_id = $dbDate;
                
                // Attempt to execute the prepared statement
                if(mysqli_stmt_execute($stmt)){
                    $result = mysqli_stmt_get_result($stmt);;
                    // echo $result;
                    if(mysqli_num_rows($result) == 1){
                        /* Fetch result row as an associative array. Since the result set
                        contains only one row, we don't need to use while loop */
                        $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                        
                        // Retrieve individual field value
                        $hours_worked = $row["hours_worked"];
                    } else{
                        // URL doesn't contain valid id. Redirect to error page
                        // header("location: error.php");
                        // exit();
                        // echo 'No result found';
                    }
                    
                } else{
                    echo "Oops! Something went wrong. Please try again later.";
                }
            }
            
            $dateToWorkedHours[$i] = [$cellContent.'-'.$this->currentYear => $hours_worked];
                            
            $content.='<li id="li-'.$cellContent.'" class="work-hour-cell">';
            $content.='<div>'.$cellContent.'</div>';
            $content.='<input type="number" name="'.$dbDate.'" class="work-hour-cell-input" value="'.$hours_worked.'">';
            $content.='</li>';
        }                                 
        $content.='</ul>';
                                 
        $content.='<div class="clear"></div>';     
             
        $content.='</div>';
        $content.='<div>';
        $content.= ''.$this->booked_hours_err;
        $content.='</div>';
        $content.='<input type="submit" name="button1" class="button" value="Submit"/>';
                 
        $content.='</div>';
        
        $content.='</form>';
        return $content;   
    }
     
    /**
    * create navigation
    */
    private function _createNavi(){

        $preWeek = intval($this->selectedWeek)-1;

        $nextWeek = intval($this->selectedWeek)+1;
         
        return
            '<div class="header">'. //Creates new url and adds month and then we check in show get requests
                '<a class="prev" href="'.$this->naviHref.'?week='.sprintf('%02d',$preWeek).'">Prev</a>'.
                    '<span class="title">'.$this->currentYear.' week-'.$this->displayedWeek.' '.'</span>'.
                '<a class="next" href="'.$this->naviHref.'?week='.sprintf('%02d',$nextWeek).'">Next</a>'.
            '</div>';
    }
         
    /**
    * create calendar week labels
    */
    private function _createLabels(){  
                 
        $content='';
         
        foreach($this->dayLabels as $index=>$label){
             
            $content.='<li class="'.($label==6?'end title':'start title').' title">'.$label.'</li>';
 
        }
         
        return $content;
    }   

    private function _getMySQLLink(){

        define('DB_SERVER', 'localhost:3306');
        define('DB_USERNAME', 'admin');
        define('DB_PASSWORD', 'admin');
        define('DB_NAME', 'php_project');
         
        /* Attempt to connect to MySQL database */
        $link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
        // echo $link;
         
        // Check connection
        if($link === false){
            die("ERROR: Could not connect. " . mysqli_connect_error());
        }
        return $link;
    }  

    private function _saveHoursBooked($workDate,$hoursBooked){
        echo $workDate.'****'.$hoursBooked.'????';
        if($hoursBooked<0 || $hoursBooked >24){
            $this->booked_hours_err = "Please enter value between 0 and 24.";
        }
        $sql = "SELECT * FROM booking WHERE booking_date = ?";
        $link = $this->_getMySQLLink();
        if($this->booked_hours_err == ''){
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "s", $param_id);
            
            // Set parameters
            $param_id = $workDate;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                $result = mysqli_stmt_get_result($stmt);;
                // echo $result;
                if(mysqli_num_rows($result) == 1){
                    /* Fetch result row as an associative array. Since the result set
                    contains only one row, we don't need to use while loop */
                    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                    
                    // Retrieve individual field value
                    $bookedHoursId = $row["id"];
                    $this->_updateHoursBooked($bookedHoursId,$workDate,$hoursBooked);
                } else{
                    $this->_insertHoursBooked($workDate,$hoursBooked);
                }
                
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        mysqli_stmt_close($stmt);
    }
    }

    private function _insertHoursBooked($dateForBooking,$hoursBooked){
        $sql = "INSERT INTO booking (booking_date, hours_worked) VALUES (?, ?)";
        $link = $this->_getMySQLLink();
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ss", $param_booking_date, $param_hours);
            
            // Set parameters
            $param_booking_date = $dateForBooking;
            $param_hours = $hoursBooked;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                echo "Success.";
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
        mysqli_stmt_close($stmt);
    }

    private function _updateHoursBooked($id,$dateForBooking,$hoursBooked){
        // Prepare an update statement
        $sql = "UPDATE booking SET booking_date=?, hours_worked=? WHERE id=?";
        $link = $this->_getMySQLLink();
         
        if($stmt = mysqli_prepare($link, $sql)){
            // Bind variables to the prepared statement as parameters
            mysqli_stmt_bind_param($stmt, "ssi", $param_booking_date, $param_hours, $param_id);
            
            // Set parameters
            $param_booking_date = $dateForBooking;
            $param_hours = $hoursBooked;
            $param_id = $id;
            
            // Attempt to execute the prepared statement
            if(mysqli_stmt_execute($stmt)){
                // Records updated successfully. Redirect to landing page
                // header("location: index.php");
                // exit();
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }
        }
         
        // Close statement
        mysqli_stmt_close($stmt);

    }
}