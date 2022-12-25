<?php
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
     
    private $currentMonth=0;
     
    private $currentDay=0;
     
    private $currentDate=null;
     
    private $daysInMonth=0;
     
    private $naviHref= null;
    private $selectedWeek = 0;
    private $displayedWeek = 0;
     
    /********************* PUBLIC **********************/  
        
    /**
    * print out the calendar
    */
    public function show() {           
         
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
        $this->currentMonth = $date->format('M');

        $this->displayedWeek = intval($date->format('W'));
         
        $content='<div id="calendar">'.
                        '<div class="box">'.
                        $this->_createNavi().
                        '</div>'.
                        '<div class="box-content">'.
                                '<ul class="label">'.$this->_createLabels().'</ul>';   
                                $content.='<div class="clear"></div>';     
                                $content.='<ul class="dates">';    
                                 
        for($i = 1; $i <= 7; $i++) {
            $cellContent = $date->add(new DateInterval('P1D'))->format('d.M');
            $this->currentDate = $currentDay;
            echo $cellContent;
            // $sql = "SELECT * FROM booking WHERE booking_date = ?";
            // if($stmt = mysqli_prepare($link, $sql)){
            //     // Bind variables to the prepared statement as parameters
            //     mysqli_stmt_bind_param($stmt, "i", $param_id);
                
            //     // Set parameters
            //     $param_id = $currentDate;
                
            //     // Attempt to execute the prepared statement
            //     if(mysqli_stmt_execute($stmt)){
            //         $result = mysqli_stmt_get_result($stmt);
        
            //         if(mysqli_num_rows($result) == 1){
            //             /* Fetch result row as an associative array. Since the result set
            //             contains only one row, we don't need to use while loop */
            //             $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
                        
            //             // Retrieve individual field value
            //             $hours_worked = $row["hours_worked"];
            //         } else{
            //             // URL doesn't contain valid id. Redirect to error page
            //             header("location: error.php");
            //             exit();
            //         }
                    
            //     } else{
            //         echo "Oops! Something went wrong. Please try again later.";
            //     }
            // }
                            
            $content.='<li id="li-'.$cellContent.'" class="work-hour-cell">';
            $content.='<div>'.$cellContent.'</div>';
            $content.='<input type="number" class="work-hour-cell-input">';
            $content.='</li>';
        }                                 
        $content.='</ul>';
        // $content.='<ul class="dates">';    
         
        // for($i = 1; $i <= 7; $i++) {
        //     $this->currentDate = $currentDay;
        //     $content.='<li id="li-'.$this->currentDate.'" class="'.($cellNumber%7==1?' start ':($cellNumber%7==0?' end ':' ')).
        //     ($cellContent==null?'mask':'').'">'.$cellContent.'</li>';
        // }                                 
        // $content.='</ul>';
                                 
        $content.='<div class="clear"></div>';     
             
        $content.='</div>';
                 
        $content.='</div>';
        return $content;   
    }
     
    /********************* PRIVATE **********************/ 
    /**
    * create the li element for ul
    */
    private function _showDay($cellNumber){
         
        if($this->currentDay==0){
            $firstDayOfTheWeek = date('N',strtotime($this->currentYear.'-'.$this->currentMonth.'-01'));
                     
            if(intval($cellNumber) == intval($firstDayOfTheWeek)){
                 
                $this->currentDay=1;
                 
            }
        }
         
        if( ($this->currentDay!=0)&&($this->currentDay<=$this->daysInMonth) ){
             
            $this->currentDate = date('Y-m-d',strtotime($this->currentYear.'-'.$this->currentMonth.'-'.($this->currentDay)));
             
            $cellContent = $this->currentDay;
             
            $this->currentDay++;   
             
        }else{
             
            $this->currentDate =null;
 
            $cellContent=null;
        }
             
         
        return '<li id="li-'.$this->currentDate.'" class="'.($cellNumber%7==1?' start ':($cellNumber%7==0?' end ':' ')).
                ($cellContent==null?'mask':'').'">'.$cellContent.'</li>';
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
}