<?php

/**
 *	Class Email
 *  -------------- 
 *  Description : encapsulates email operations & properties
 *	Written by  : ApPHP
 *	Version     : 1.0.5
 *  Updated	    : 29.02.2012
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *
 *  1.0.5
 *      - changed " with '
 *      - fixed bug - missed ;
 *      -
 *      -
 *      -
 *  1.0.4
 *  	- added defition of type, according to mailer type
 *  	- error fixed in defining $this->type
 *  	- added content-type and charset for simple type
 *  	- improved headers for 'simple' type
 *  	- removed unexpected from in $headers
 *  1.0.3
 *      - EC_DEFAULTCHARSET changed to UTF-8
 *      - added $additional_parameters = '-f '.$this->from; to remove sent-by
 *      - added replyTo public property for the class
 *      - added type of sending type = 'standard'
 *      - added MIME-Version for non-standard sending
 *      
 *	
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 *	__construct
 *	__destruct
 *	IsComplete
 *	Send
 *	
 **/



  
define('EC_NEWLINE', "\r\n");                     /* newline character(s) */  
define('EC_XMAILER', 'PHP-EMAIL-CLASS (ApPHP)');  /* the unique X-Mailer identifier */
define('EC_DEFAULTCHARSET', 'UTF-8');             /* the default charset values text and HTML */
define('EC_UNIQEID', 'SC_EMAIL');                 /* unique ID */  												    
												    

class Email{
  
	// text email or a HTML email
	public $textOnly = true; 
	// the charset of the email
	public $charset = null; 
	// (string) the subject of the email
	public $replyTo = null; 
	// (string) body content for the email. Plain text or HTML based on the 'textOnly'
	public $content = null;
	// (array) email attachment instances
	public $arrAttachments;	
	// any header information that used when sending email
	private $headers = null; 

	private $to = null;  
	// (string) recipiant addresses to get a copy - can be a comma separated
	private $cc = null; 
	// (string) recipiant addresses to get a hidden copy - can be a comma separated
	private $bcc = null; 
	// (string) the email address of sender
	private $from = null; 
	// (string) the email for reply
	private $subject = null;	
	// type of sending
	private $type = 'standard';	

	//==========================================================================
	// Class Constructor
	// 		@param $to
	//      @param $from
	//      @param $subject
	//      @param $headers
	//==========================================================================
	function __construct($to=null, $from=null, $subject=null, $headers=null) 
	{
		global $objSettings;

		$this->to = $to; 
		$this->from = $from; 
		$this->subject = $subject; 
		$this->headers = $headers;

		$mailer = $objSettings->GetParameter('mailer_type');
		if($mailer == 'php_mail_standard'){
			$this->type = 'standard';		
		}elseif($mailer == 'php_mail_simple'){
			$this->type = 'simple';		
		}else{
			$this->type = 'standard';	
		}

		$this->arrAttachments = array();    
		$this->arrAttachments['text'] = null;
		$this->arrAttachments['html'] = null;
	} 
	
	//==========================================================================
	// Class Destructor
	//==========================================================================
	function __destruct()
	{
		// echo 'this object has been destroyed';
	}	

	/**
	 * Check whether or not the email message is ready to be sent
	*/
	function IsComplete() 
	{ 
		return (strlen(trim($this->to)) > 0 && strlen(trim($this->from)) > 0); 
	} 

	/**
	 * Send the email message
	 * Returns: Boolean 
	*/
    function Send() 
    {
		// if message is not ready to send no message will be sent
		if(!$this->IsComplete()) return false;

		// get unique boundry identifier to separate attachments
		$the_boundary = '-----'.md5(uniqid(EC_UNIQEID)); 		
		
		// -- HEADERS --- 
		// add from email address and the current date of sending
		$headers = 'Date: '.date('r', time()).EC_NEWLINE;

		// add reply-to to the headers, if not empty
		if(strlen(trim(strval($this->replyTo))) > 0) $headers .= 'Reply-To: '.$this->replyTo.EC_NEWLINE;

		// add CC field to the headers, if not empty
		if(strlen(trim(strval($this->cc))) > 0) $headers .= 'CC: '.$this->cc.EC_NEWLINE;

		// add BCC field to the headers, if not empty
		if(strlen(trim(strval($this->bcc))) > 0) $headers .= 'BCC: '.$this->bcc.EC_NEWLINE;

		// add custom headers before important information 
		if($this->headers != null && strlen(trim($this->headers)) > 0) $headers .= $this->headers.EC_NEWLINE; 

		// is this email is mixed HTML and text or both
		$is_multipart_alternative = ($this->arrAttachments['text'] != null &&
								     $this->arrAttachments['html'] != null);

	    // set the correct MIME type
        $base_content_type = 'multipart/'.($is_multipart_alternative ? 'alternative' : 'mixed');

		if($is_multipart_alternative){
			// add the text and html versions of the content
			// code here ...
		}else{
			// only html or text email	
			$the_email_type = 'text/'.($this->textOnly ? 'plain' : 'html'); 
		    if($this->charset == null) $this->charset = EC_DEFAULTCHARSET;
		
			// add the encoding header information to the body
			$the_body = '--'.$the_boundary.EC_NEWLINE. 
						'Content-Type: '.$the_email_type.'; charset='.$this->charset.EC_NEWLINE.
						'Content-Transfer-Encoding: 8bit'.EC_NEWLINE.EC_NEWLINE.
						$this->content.EC_NEWLINE.EC_NEWLINE; 		
		}
		// boundry marker
	    $the_body .= '--'.$the_boundary.'--';
		
		// send email according to types
		if($this->type == 'standard'){
			// set headers
			$headers .= 'From: '.$this->from.EC_NEWLINE.
						'X-Mailer: '.EC_XMAILER.EC_NEWLINE. 
						'MIME-Version: 1.0'.EC_NEWLINE. 
						'Content-Type: '.$base_content_type.'; '.
						'boundary="'.$the_boundary.'"'.EC_NEWLINE.EC_NEWLINE; 
			// removes Sent-by
			$additional_parameters = '-f '.$this->from;	
			// try to send the email message
			return @mail($this->to, $this->subject, $the_body, $headers, $additional_parameters);			
		}else{			
			// another version for some public hostings
			// to send HTML mail, the Content-type header must be set
			$headers .= 'MIME-Version: 1.0'.EC_NEWLINE.
						'Content-Type: '.$the_email_type.'; charset='.$this->charset.EC_NEWLINE.
			            'From: '.$this->from.EC_NEWLINE.
					    'Reply-To: '.$this->from.EC_NEWLINE.
					    'X-Mailer: PHP/'.phpversion();
						
			///echo '<br>'.$this->to.'--'.$this->subject.'--'.'<br>'.$this->content.'--'.'<br>'.$headers;
			// try to send the email message
			return @mail($this->to, $this->subject, $this->content, $headers);
		}
    } 
} 
