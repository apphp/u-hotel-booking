<?php
################################################################################
##              -= YOU MAY NOT REMOVE OR CHANGE THIS NOTICE =-                 #
## --------------------------------------------------------------------------- #
##  ApPHP Tabs Advanced version 2.0.2 (20.02.2010)                             #
##  Developed by:  ApPHP <info@apphp.com>                                      #
##  License:       GNU GPL v.2                                                 #
##  Site:          http://www.apphp.com/php-tabs/                              #
##  Copyright:     ApPHP Tabs (c) 2009. All rights reserved.                   #
##                                                                             #
################################################################################
/**
 * class Tabs
    represents a set of tabs
    last date modified: 12.02.2011
      - fixed: if($newCurrTab=="") error
 *
 */
 class Tabs
 {
        // PUBLIC
        // -------
        // constructor
        // AddTab
        // AddTabAction
        // Display
        // Enable
        // Disable
        // GetNumChildren
        // GetId
        // GetCaption
        // SetCaption
        // SetWidth
        // SetHeight
        // GetWidth
        // GetHeight
        // SetAlign
        // GetAlign
        // SetDefaultTab
        // GetDefaultTabId
        // SetContainerColor
        // SetBorderWidth
        // SetBorderColor
        // SetStyle
        // SetPath
        // SetSubmissionType
        // Debug
        // GetDebug
        // IsRefreshSelectedTabsAllowed
        // AllowRefreshSelectedTabs

        // PRIVATE
        // --------
        // LoadFiles
        // SetDisplayParameters
        // GetFormattedMicrotime
        // ShowDebugInformation
        // ToHex

    //--- PRIVATE DATA MEMBERS --------------------------------------------------
    private $tabs;
    private $numOfSelectedTabs;
    private $numChildren=0;
    private $caption;
    private $id;
    private $align="left";
    private $width="auto";
    private $height="auto";
    private $isDebug=false;
    private $containerColor="";
    private $borderWidth="";
    private $borderColor="black";
    private $submissionType="post";
    private $style;
    private $defaultTabId="";
    private $path="";
    private $refreshSelectedTabsAllowed=false;
    private $httpVars;
    private $queryString;
    private $token;

    //--- CONSTANTS --------------------------------------------------
    const tabsVersion="2.0.2";


    /**
	 * Creates new set of tabs
            @param $id - this set's id
            @param $style - style's name (corresponds to subfolder of the same name within 'styles' folder)
            @param $path - path to tabs.class.php file
            @param $caption - this set's name
	*
	*/
    public function __construct($id=1,$style="xp",$path="",$query_string,$caption="")
    {
       if(!is_numeric($id)||!is_integer($id))
          $id=1;
       $this->style=$style;
       $this->path=$path;
       $this->caption=$caption;
       $this->tabs=array();
       $this->id=$id;
       $this->httpVars = array();
       $this->queryString = $query_string;
       $this->token = "";

    }

    public function SetToken($val = "")
    {
         $this->token = $val;
    }

    /**
	* Adds a new child tab to this set of tabs (calculates new tab's id and calls private function AddTabAction)
	      @param $caption - text on the tab
	      @param $file - file associated with this tab
	      @param $enabled - is tab enabled or disabled
	*
	*/
    public function AddTab($caption,$file="",$enabled=true)
    {
         $id=$this->GetId()."_".++$this->numChildren;
         return $this->AddTabAction($caption,$file,$enabled,$id);
    }

    /**
	* Adds a new child tab to this set of tabs
	      @param $caption - text on the tab
	      @param $file - file associated with this tab
	      @param $enabled - is tab enabled or disabled
	      @param $id - tab's id
	*
	*/
    public function AddTabAction($caption,$file="",$enabled=true,$id)
    {
       if(preg_match("/^[0-9|_]+$/",$id)==0)
          return;
       if($enabled === true || strtolower($enabled) == "true")
          $enabled=true;
       else $enabled=false;
       $this->tabs[$id]=new Tab($caption,$id,$file,$enabled,$this);
       return $this->tabs[$id];
    }

    /**
	 * Loads CSS and JS files
	 */
	private function LoadFiles()
	{
      if(!file_exists($this->path."styles/".$this->style."/style.css")) $this->style="xp";
      if(file_exists($this->path."styles/".$this->style."/style.css"))
         echo  "<link href='".$this->path."styles/".$this->style."/style.css' rel='stylesheet' type='text/css' />\n";
      if(file_exists($this->path."styles/common.css"))
         echo  "<link href='".$this->path."styles/common.css' rel='stylesheet' type='text/css' />\n";
      if(file_exists($this->path."js/script.js"))
         echo  "<script type='text/javascript' src='".$this->path."js/script.js'></script>";
    }

    /**
	* Sets display parameters
	*
	*/
	private function SetDisplayParameters()
	{
	   echo "\n<style><!--\n";
	   if($this->width!="auto"||$this->align!="left")
	   {
	      echo "#tabs {\n";
	      if($this->width!="auto")
             echo "\twidth: ".$this->width.";\n";
          if($this->align!="left")
             echo "\ttext-align: ".$this->align.";\n";
          echo "}\n";
	   }
       echo "#container {\n";
       if($this->height!="auto")
          echo "\theight: ".$this->height.";\n";
       if($this->borderWidth!="")
       echo "\tborder-width: ".$this->borderWidth.";\n";
       if($this->borderWidth!=0)
          echo "\tborder-color: ".$this->borderColor.";\n";
       if($this->containerColor!="")
          echo "\tbackground-color: ".$this->containerColor.";\n";


       echo "}\n";
       if($this->refreshSelectedTabsAllowed)
          echo ".tabsel {\n\tcursor:pointer;\n}\n";
       echo "-->\n</style>\n";
    }

    /**
	* Displays this set of tabs
	     @param $mode - display mode ("link" or default)
	*
	*/
    public function Display($mode="")
    {
       if($this->caption!="")
          echo "<span id='caption'>".$this->caption."</span><br /><br />";
       $startTime = $this->GetFormattedMicrotime();

       $this->LoadFiles();
       $this->SetDisplayParameters();

       //picking up id parameter that denotes which tab was selected
       $preselected=isset($_REQUEST['tabid']) ? $_REQUEST['tabid'] : "";
       if(!isset($this->tabs[$preselected]))
          $preselected="";
       $preselected_tabs=explode("_",$preselected);

       //displaying tabs
       echo "<div id='tabs'>";
       if($this->submissionType == "post"){
         $params = $this->queryString;
         foreach($this->httpVars as $key){
            $params .= "&".$key."=".$_REQUEST[$key];
         }         
         
       }       
       echo "\n<form name='frmTabs' id='frmTabs' action='".$_SERVER["SCRIPT_NAME"].$params."' method='".$this->submissionType."'>\n";
       if($this->submissionType == "get"){
         foreach($this->httpVars as $key){
           echo "<input type='hidden' id='".$key."' name='".$key."' value='".$_REQUEST[$key]."' />\n";
         }         
       }
       echo "<input type='hidden' id='tabid' name='tabid'/>\n";
       if($this->token != "") echo "<input type='hidden' name='token' value='".$this->token."' />\n";
       $currLevel=1;
       $currTab=$this;

       while($currTab->GetNumChildren()>0)
       {
       	  echo "<ul class='menu'>\n";
          $newCurrTab="";
          for($i=1;$i<$currTab->GetNumChildren()+1;$i++)
          {
             $tab=$this->tabs[$currTab->GetId()."_".$i];
             if(empty($newCurrTab))
             {
                if($preselected!=""&&$currLevel<count($preselected_tabs))
                {
                   if($i==$preselected_tabs[$currLevel])
                   {
                      if(!$tab->IsEnabled())
                         $preselected="";
                      else
                      {
                         $newCurrTab=$tab;
                         $tab->Select();
                      }
                   }
                }
                else if ($tab->IsEnabled()&&($currTab->GetDefaultTabId()==""||$currTab->GetDefaultTabId()==$tab->GetId()))
                {
                   $newCurrTab=$tab;
                   $tab->Select();
                }
             }
             $tab->Display($mode);
          }
          $currTab=$newCurrTab;
          $currLevel++;
          echo "\n</ul>";
       }
       echo "\n</form>";
       
       //displaying content associated with selected tab
       echo "\n<div id='container'>\n";
       $newCurrTab->ShowContent($this->isDebug);
       echo "\n</div>";
       
       echo "\n</div>";

       if($this->isDebug)
          $this->ShowDebugInformation($startTime);

       echo "\n<!-- END This script was generated by tabs.class.php v.".Tabs::tabsVersion."(http://www.apphp.com/php-tabs/index.php) END -->\n";

    }

     /**
	 *
	 */
     public function SetHttpVars($param = array())
     {
        $this->httpVars = $param;
     }


    /**
	* Enables a tab
	     @param $tab - tab to enable
	*
	*/
    public function Enable($tab)
    {
       if(!is_a($tab,"Tab"))
       {
          if($this->isDebug)
             echo "<span style='color:#ff0000'>Error: 'Enable' function parameter is not a valid Tab object</span>";
          return;
       }
       foreach($this->tabs as $tempTab)
       {
          if($tab->GetId()==$tempTab->GetId())
             $tab->Enable();
       }
    }

    /**
	* Disables a tab
	     @param $tab - tab to disable
	*
	*/
    public function Disable($tab)
    {
       if(!is_a($tab,"Tab"))
       {
          if($this->isDebug)
             echo "<span style='color:#ff0000'>Error: 'Disable' function parameter is not a valid Tab object</span>";
          return;
       }
       foreach($this->tabs as $tempTab)
       {
          if($tab->GetId()==$tempTab->GetId())
             $tab->Disable();
       }
    }

    /**
	* Returns number of tabs on the first level
	*
	*/
	public function GetNumChildren()
    {
       return $this->numChildren;
    }

    /**
	* Returns id of this set of tabs
	*
	*/
    public function GetId()
    {
       return $this->id;
    }

    /**
	* Returns caption of this set of tabs
	*
	*/
    public function GetCaption()
    {
    	return $this->caption;
    }

    /**
	* Sets caption of this set of tabs
	     @param $caption - new caption
	*
	*/
    public function SetCaption($caption)
    {
    	$this->caption=$caption;
    }

    /**
	* Sets container's width
	     @param $width - new width
	*
	*/
    public function SetWidth($width)
    {
    	$width=strtolower($width);
    	if($width=="auto")
    	{
    	   $this->width="auto";
    	   return;
    	}
    	if(substr($width,-1)=="%")
    	{
    	   $unit="%";
    	   $number=substr($width,0,strlen($width)-1);
        }
        else
        {
        	$unit=substr($width,-2);
        	if($unit=="px"||$unit=="pt"||$unit=="in")
        	   $number=substr($width,0,strlen($width)-2);
        	else
        	{
        	   $unit="px";
        	   $number=$width;
        	}
        }
        if(preg_match("/^[0-9]+$/",$number)==0||$number<0)
           return;
    	if($unit=="%"&&$number>100)
    	   return;
    	$this->width=$number.$unit;
    }

    /**
	* Sets container's height
	     @param $height - new height
	*
	*/
    public function SetHeight($height)
    {
    	$height=strtolower($height);
    	if($height=="auto")
    	{
    	   $this->height="auto";
    	   return;
    	}
        $unit=substr($height,-2);
        if($unit=="px"||$unit=="pt"||$unit=="in")
           $number=substr($height,0,strlen($height)-2);
        else
        {
           $unit="px";
           $number=$height;
        }
        if(preg_match("/^[0-9]+$/",$number)==0||$number<0)
           return;
    	$this->height=$number.$unit;

    }

    /**
	* Returns container's width
	*
	*/
    public function GetWidth()
    {
    	return $this->width;
    }

    /**
	* Returns container's height
	*
	*/
    public function GetHeight()
    {
    	return $this->height;
    }

    /**
	* Sets mode of alignment within the container
	     @param $align - left, center or right
	*
	*/
    public function SetAlign($align)
    {
    	if(strtolower($align)=="center"||strtolower($align)=="left"||strtolower($align)=="right")
    	   $this->align=$align;
    }

    /**
	* Returns mode of alignment within the container
	*
	*/
    public function GetAlign()
    {
    	return $this->align;
    }

    /**
	* Sets default tab
	     @param $defaultTab - new default tab
	*
	*/
    public function SetDefaultTab($defaultTab)
    {
    	if(!is_a($defaultTab,"Tab"))
        {
           if($this->isDebug)
              echo "<span style='color:#ff0000'>Error: 'SetDefaultTab' function parameter is not a valid Tab object</span>";
           return;
        }
    	$this->defaultTabId=$defaultTab->GetId();
    }

    /**
	* Returns default tab's id
	*
	*/
     public function GetDefaultTabId()
     {
    	return $this->defaultTabId;
     }

    /**
	* Sets container's color
	     @param $main - new color when one parameter is passed or red component when three parameters are passed
	     @param $green - green component
	     @param $blue - blue component
	*
	*/
    public function SetContainerColor($main,$green="",$blue="")
    {
    	if(is_numeric($main)&&$main>=0&&$main<=255&&
           is_numeric($green)&&$green>=0&&$green<=255&&
           is_numeric($blue)&&$blue>=0&&$blue<=255)
        {
        	$red=Tabs::ToHex($main);
        	$green=Tabs::ToHex($green);
        	$blue=Tabs::ToHex($blue);
            $this->containerColor="#".$red.$green.$blue;
        }
    	else $this->containerColor=$main;
    }

    /**
	* Sets border's width
	     @param $borderWidth - new border's width
	*
	*/
    public function SetBorderWidth($borderWidth)
    {
    	$borderWidth=strtolower($borderWidth);
        $unit=substr($borderWidth,-2);
        if($unit=="px"||$unit=="pt"||$unit=="in")
           $number=substr($borderWidth,0,strlen($borderWidth)-2);
        else
        {
           $unit="px";
           $number=$borderWidth;
        }
        if(preg_match("/^[0-9]+$/",$number)==0||$number<0)
           return;
    	if($unit=="%"&&$number>100)
    	   return;
    	$this->borderWidth=$number.$unit;
    }

    /**
	* Sets border's color
	     @param $main - new color when one parameter is passed or red component when three parameters are passed
	     @param $green - green component
	     @param $blue - blue component
	*
	*/
    public function SetBorderColor($main,$green="",$blue="")
    {
    	if(is_numeric($main)&&$main>=0&&$main<=255&&
           is_numeric($green)&&$green>=0&&$green<=255&&
           is_numeric($blue)&&$blue>=0&&$blue<=255)
        {
            $red=Tabs::ToHex($main);
        	$green=Tabs::ToHex($green);
        	$blue=Tabs::ToHex($blue);
            $this->borderColor="#".$red.$green.$blue;
        }
    	else $this->borderColor=$main;
    }

    /**
	* Sets style
	     @param $style - new style
	*
	*/
    public function SetStyle($style)
    {
    	if(file_exists($this->path."styles/".$style."/style.css"))
    	   $this->style=$style;
    }

    /**
    *   Sets current path
         @param $path - new path
    *
    */
    public function SetPath($path)
    {
    	$this->path=$path;
    }

    /**
	 * Gets formatted microtime
	 */
     private function GetFormattedMicrotime(){
        list($usec, $sec) = explode(' ', microtime());
        return ((float)$usec + (float)$sec);
    }

    /**
	 * Sets form submission type
	 * @param $submission_type
	 */
	public function SetSubmissionType($submission_type = "post")
	{
		if(strtolower($submission_type) == "get") $this->submissionType = "get";
		else $this->submissionType = "post";
	}

    /**
	 * Sets debug mode
	 * @param $mode
	 */
	public function Debug($mode = false)
	{
		if($mode === true || strtolower($mode) == "true") $this->isDebug = true;
	}

	/**
	 * Returns debug mode
	 *
	*/
	public function GetDebug()
	{
		return $this->isDebug;
	}

	/**
	 * Returns if refreshing selected tabs is enabled
	 *
	*/
	public function IsRefreshSelectedTabsAllowed()
	{
		return $this->refreshSelectedTabsAllowed;
	}

    /**
	 * Allows or prohibits to refresh selected tabs
	 * @param $allow - true or false
	 */
	public function AllowRefreshSelectedTabs($allow = false)
	{
		if($allow === true || strtolower($allow) == "true") $this->refreshSelectedTabsAllowed = true;
	}

    /**
	 * Shows debug information
	 * @param $startTime - time when the script started
	*/
    private function ShowDebugInformation($startTime)
    {
       $endTime = $this->GetFormattedMicrotime();
	   echo "<div style='margin: 10px auto; text-align:left; color:#000096;'>";

	   echo "Debug Info: (Total running time: ".round((float)$endTime - (float)$startTime, 6)." sec.) <br />========<br />";
	   echo "TABS: <br />--------<br />";
	   echo "<pre>";
	   echo "<table style='color:#000096;'>";
       foreach($this->tabs as $tab)
       {
          echo "<tr>";
          echo "<td>".$tab->GetId()."</td>";
          echo "<td>".$tab->GetCaption()."</td>";
          echo "</tr>";
       }
       echo "</table>";
       echo "</pre>";
	   echo "<br />GET: <br />--------<br />";
	   echo "<pre>";
	   print_r($_GET);
	   echo "</pre><br />";
	   echo "POST: <br />--------<br />";
	   echo "<pre>";
	   print_r($_POST);
	   echo "</pre><br />";
	   echo "</div>";
    }

	/**
	 * Converts a decimal number to two-digit hexadecimal
	 * @param $number - number to be converted
	 */
	private static function ToHex($number)
	{
	   if(strlen(dechex($number))>1)
	      return dechex($number);
	   else return "0".dechex($number);
    }
 }


 /**
 * class Tab
      represents a separate tab
      last date modified: 20.02.2010
 *
 */
class Tab
{
        // PUBLIC
        // -------
        // constructor
        // AddTab
        // Display
        // ShowContent
        // IsSelected
        // Select
        // Deselect
        // IsEnabled
        // Enable
        // Disable
        // GetCaption
        // GetId
        // GetNumChildren
        // SetDefaultTab
        // GetDefaultTabId

        // PRIVATE
        // --------
        // IsPicture
        // IsHTML
        // IsPHP

    //--- PRIVATE DATA MEMBERS --------------------------------------------------
    private $caption;
    private $enabled=true;
    private $selected=false;
    private $level;
    private $id;
    private $parent;
    private $defaultTabId="";
    private $numChildren=0;


    /**
	 * Creates a new tab
	        @param $caption - tab's caption
            @param $id - tab's id
            @param $file - name of the file associated with this tab
            @param $enabled - is this tab enabled or disabled
            @param $parent - set of tabs which contains this tab
	*
	*/
    function __construct($caption,$id,$file="",$enabled=true,$parent)
	{
           if(preg_match("/^[0-9|_]/",$id)==0)
              $id=0;
           $this->file=$file;
           $this->id=$id;
           $this->caption=$caption;
           if($enabled === true || strtolower($enabled) == "true")
              $enabled=true;
           else $enabled=false;
           $this->parent=$parent;
	}

   /**
	* Adds a new child tab to this tab
	      @param $caption - text on the tab
	      @param $file - file associated with this tab
	      @param $enabled - is tab enabled or disabled
	*
	*/
    public function AddTab($caption,$file="",$enabled=true)
    {
        if(!is_a($this->parent,"Tabs"))
        {
           echo "<span style='color:#ff0000'>Error: tab ".$this->caption." has no valid parent object</span>";
           return;
        }
        $id=$this->GetId()."_".++$this->numChildren;
        return $this->parent->AddTabAction($caption,$file,$enabled,$id);
    }

    /**
	* Displays the tab
	     @param $mode - display mode ("links" or default)
	*
	*/
	public function Display($mode="")
	{
        //choosing appropriate style
        if($this->selected)
           $style="sel";
        else if($this->enabled)
           $style="";
        else $style="dis";
        //displaying in links mode
        if(strcmp($mode,"link")==0)
        {
           $request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : "";
           if(strpos($request_uri,"tabid="))
              $request_uri=substr($request_uri,0,strpos($request_uri,"tabid=")-1);
           if(strpos($request_uri,"?"))
              $href=$request_uri."&tabid=".$this->id;
           else $href=$request_uri."?tabid=".$this->id;
           if($this->IsEnabled())
              echo "<a class=\"href".$style."\" href=".$href." onclick='select(this)' id='",$this->id,"'>".$this->caption."</a>&nbsp&nbsp&nbsp&nbsp";
           else echo $this->caption."&nbsp&nbsp&nbsp&nbsp";
        }
        //displaying in default mode
        else
        {
        	echo "<li class='element'><a";
        	if($this->IsEnabled()&& (!$this->IsSelected()||$this->parent->IsRefreshSelectedTabsAllowed()) )
        	   echo " onclick='__TabsPostBack(this)' onmouseover='Semiselect(this)' onmouseout='Semideselect(this)'";
            echo " id='",$this->id,"' class='tab".$style."'><span class='inner".$style."'>".$this->caption."</span></a></li>";
        }
    }



    /**
	* Shows contents of the file which is associated with this tab
	*
	*/
    public function ShowContent()
    {
         ///if($this->file == "") return true;
         
         echo $this->file;
         return;
      
         
         echo"<br />\n\n";
         if(!file_exists($this->file))
         {
           if($this->parent->GetDebug())
              echo "File '".$this->file."' not found";
           return;
         }
         if($this->IsPicture())
    	    echo "<img src='",$this->file,"'>";
    	 else if($this->IsPHP())
    	 {
    	    require_once($this->file);
    	 }
    	 else if($this->IsHTML())
    	 {
            $str = file_get_contents($this->file);
    	    if(preg_match("/<head.*?>(.+?)<\/head>/si",$str,$head)!=0)
    	    {
               if(preg_match_all("/<script.*?>(.*?)<\/script>/si",$head[1],$scripts)!=0)
    	          foreach($scripts[0] as $script)
    	             echo $script;
         	   if(preg_match_all("/<style.*?>(.*?)<\/style>/si",$head[1],$styles)!=0)
    	          foreach($styles[0] as $style)
    	             echo $style;
    	    }
    	    if(preg_match("/<body.*?>(.+?)<\/body>/si",$str,$body)!=0)
        	   print_r($body[1]);
        	else print_r($str);
    	 }
    	 else echo file_get_contents($this->file);
    }

     /**
	 * Checks if the tab is selected
	 *
	 */
     public function IsSelected()
     {
        return $this->selected;
     }

     /**
	 * Selects the tab
	 *
	 */
     public function Select()
     {
        if(!$this->IsEnabled())
           $this->Enable();
        $this->selected=true;
     }

     /**
	 * Deselects the tab
	 *
	 */
     public function Deselect()
     {
        $this->selected=false;
     }

     /**
	 * Checks if the tab is enabled
	 *
	 */
     public function IsEnabled()
     {
        return $this->enabled;
     }

     /**
	 * Enables the tab
	 *
	 */
     public function Enable()
     {
        $this->enabled=true;
     }

     /**
	 * Disables the tab
	 *
	 */
     public function Disable()
     {
        $this->enabled=false;
     }

     /**
	 * Returns the tab's caption
	 *
	 */
     public function GetCaption()
     {
        return $this->caption;
     }

     /**
	 * Returns the tab's id
	 *
	 */
     public function GetId()
     {
        return $this->id;
     }

     /**
	 * Returns amount of subtabs associated with this tab
	 *
	 */
     public function GetNumChildren()
     {
        return $this->numChildren;
     }

    /**
	* Sets default tab
	     @param $defaultTab - new default tab
	*
	*/
     public function SetDefaultTab($defaultTab)
     {
    	if(!is_a($defaultTab,"Tab"))
        {
           if($this->parent->GetDebug())
              echo "<span style='color:#ff0000'>Error: 'SetDefaultTab' function parameter is not a valid Tab object</span>";
           return;
        }
    	$this->defaultTabId=$defaultTab->GetId();
     }

     /**
	* Returns default tab's id
	*
	*/
     public function GetDefaultTabId()
     {
    	return $this->defaultTabId;
     }

     /**
	 * Checks if file associated with this tab is a graphic file
	 *
	 */
     private function IsPicture()
     {
        $extension=strtolower(substr(strrchr($this->file,"."),1));
        if($extension=="jpg"||$extension=="gif"||$extension=="bmp"||$extension=="tif"||$extension=="png"||$extension=="jpeg")
           return true;
        else return false;
     }

     /**
	 * Checks if file associated with this tab is a hypertext file
	 *
	 */
     private function IsHTML()
     {
        $extension=strtolower(substr(strrchr($this->file,"."),1));
        if($extension=="htm"||$extension=="xml"||$extension=="html")
           return true;
        else return false;
     }

     /**
	 * Checks if file associated with this tab is a PHP file
	 *
	 */
     private function IsPHP()
     {
        $extension=strtolower(substr(strrchr($this->file,"."),1));
        if($extension=="php")
           return true;
        else return false;
     }


}
 ?>