<?php
/**
* @project uHotelBooking
* @copyright (c) 2019 ApPHP
* @author ApPHP <info@apphp.com>
* @site http://www.hotel-booking-script.com
* @license http://hotel-booking-script.com/license.php
*/

// *** Make sure the file isn't accessed directly
defined('APPHP_EXEC') or die('Restricted Access');
//--------------------------------------------------------------------------

$content = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
  <head>
    <title>{SUBJECT}</title>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
  </head>
  <body style="background-color:#DDDDDD;">
    <center>
      <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" id="bodyTable" style="height:100%;border-collapse:collapse;mso-table-lspace:0pt;mso-table-rspace:0pt;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;margin:0;padding:0;width:100%;">
        <tr>
          <td align="center" valign="top" style="mso-line-height-rule:exactly;-ms-text-size-adjust:100%;-webkit-text-size-adjust:100%;height:100%;margin:0;padding:10px;width:100%;border-top:0;">
            
            <div style="background-color:#DDDDDD;width:100%;">
              <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#DDDDDD" style="background-color:#DDDDDD;border-collapse:collapse;">
                <tbody>
                  <tr>
                    <td bgcolor="#DDDDDD" align="center">
                      <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#590F32" align="center" style="border-collapse:collapse;max-width:600px;" class="m_-7210621922111406428responsive-table">
                        <tbody>
                          <tr>
                            <td width="100%" bgcolor="#ffffff" style="text-align:left;padding:30px 30px;width:100%;color:#FFFFFF;font-family:Calibri, sans-serif;font-size:16px;background-color:#F67C79;background-color:#ffffff;border-top:10px solid #d8a063">
                              <a href="'.get_base_url().'"><img src="'.get_base_url().'templates/'.Application::Get('template').'/images/logo_email.png" width="100%" style="max-width:360px;margin-bottom:20px;" alt="logo"><br>
                              <img src="'.get_base_url().'templates/'.Application::Get('template').'/images/banner.jpg" width="100%" style="max-width:560px;" alt="banner"></a> 
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div style="background-color:#DDDDDD;width:100%;">
              <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#DDDDDD" style="background-color:#DDDDDD;border-collapse:collapse;">
                <tbody>
                  <tr>
                    <td bgcolor="#DDDDDD" align="center">
                      <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" align="center" style="border-collapse:collapse;max-width:600px;" class="m_-7210621922111406428responsive-table">
                        <tbody>
                          <tr>
                            <td width="100%" bgcolor="#FFFFFF" style="text-align:left;background-color:#FFFFFF;padding:20px 30px;width:100%;color:#344B61;font-family:Calibri, sans-serif;font-size:14px;font-weight:normal !important;padding-bottom:50px;max-width:500px;">
                                {TEMPLATE_EMAIL_CONTENT}
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div style="background-color:#DDDDDD;width:100%;">
              <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#DDDDDD" style="background-color:#DDDDDD;border-collapse:collapse;">
                <tbody>
                  <tr>
                    <td bgcolor="#DDDDDD" align="center">
                      <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#ffffff" align="center" style="border-collapse:collapse;max-width:600px;" class="m_-7210621922111406428responsive-table">
                        <tbody>
                          <tr>
                            <td align="center"  width="100%" style="text-align:center;">
                              <table align="center"  width="70%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;">
                                  <tbody>
                                      <tr>
                                          <td align="center" style="border-top:1px solid #d8a063;border-bottom:0px solid #ccc;">
                                          </td>
                                      </tr>
                                  </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div style="background-color:#DDDDDD;width:100%;">
              <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#DDDDDD" style="background-color:#DDDDDD;border-collapse:collapse;">
                <tbody>
                  <tr>
                    <td bgcolor="#DDDDDD" align="center">
                      <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#FFFFFF" align="center" style="border-collapse:collapse;max-width:600px;" class="m_-7210621922111406428responsive-table">
                        <tbody>
                          <tr>
                            <td width="100%" bgcolor="#FFFFFF" style="text-align:left;background-color:#FFFFFF;padding:20px 30px;width:100%;color:#344B61;font-family:Calibri, sans-serif;font-size:18px;font-weight:normal !important; line-height:140%">
                              <h1 style="color:#D8A063;font-family:\"open sans\", arial, sans-serif;margin-bottom:10px;">Contact Us</h1>
                              464 Edware Road, Depoartment 34 <br>
                              London, W2 1AH <br>
                              02030513631 <br>
                              <a href="mailto:info@hotel-booking-script.com" style="text-decoration:none;color:#344B61">info@hotel-booking-script.com</a><br>
                              <a href="'.get_base_url().'" style="text-decoration:none;color:#344B61">www.hotel-booking-script.com</a>
                              <br>
                             </td>
                            </tr>
                          </tbody>
                        </table>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            <div style="background-color:#DDDDDD;width:100%;">
              <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#DDDDDD" style="background-color:#DDDDDD;border-collapse:collapse;">
                <tbody>
                  <tr>
                    <td bgcolor="#DDDDDD" align="center">
                      <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#ffffff" align="center" style="border-collapse:collapse;max-width:600px;" class="m_-7210621922111406428responsive-table">
                        <tbody>
                          <tr>
                            <td align="center"  width="100%" style="text-align:center;">
                              <table align="center"  width="100%" cellspacing="0" cellpadding="0" border="0" style="border-collapse:collapse;padding:20px 20px;">
                                  <tbody>
                                      <tr>
                                          <td align="center" style="border-top:1px solid #d8a063;border-bottom:0px solid #ccc;">
                                          </td>
                                      </tr>
                                  </tbody>
                              </table>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div style="background-color:#DDDDDD;width:100%;">
              <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#DDDDDD" style="background-color:#DDDDDD;border-collapse:collapse;">
                <tbody>
                  <tr>
                    <td bgcolor="#DDDDDD" align="center">
                      <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#ffffff" align="center" style="border-collapse:collapse;max-width:600px;" class="m_-7210621922111406428responsive-table">
                        <tbody>
                          <tr>
                            <td width="30%" bgcolor="#d8a063" style="text-align:right;background-color:#d8a063;padding:20px 10px;width:30%;color:#e0e0e0;font-family:\"Proxima nova\", sans-serif;font-size:13px;padding-bottom:10px;">
                              <span style="font-size:22px">
                                <span style="font-family:arial,helvetica neue,helvetica,sans-serif">
                                  Follow Us... 
                                </span>
                                <br>
                              </span>
                            </td>
                            <td width="60%" bgcolor="#d8a063" style="text-align:left;background-color:#d8a063;padding:20px 10px;padding-bottom:10px;width:60%;color:#e0e0e0;font-family:\"Proxima nova\", sans-serif;font-size:13px;" >
                              <span style="font-size:20px">
                                <span style="font-family:arial,helvetica neue,helvetica,sans-serif">
                                    <a href="#"><img src="'.get_base_url().'templates/'.Application::Get('template').'/images/fb.png" width="40" alt="Facebook" class="CToWUd"></a>&nbsp;
                                    <a href="#"><img src="'.get_base_url().'templates/'.Application::Get('template').'/images/twitter.png" width="40" alt="Twitter" class="CToWUd"></a>&nbsp;
                                    <a href="#"><img src="'.get_base_url().'templates/'.Application::Get('template').'/images/linkedin.png" width="40" alt="Linkedin" class="CToWUd"></a>&nbsp;
                                    <a href="#"><img src="'.get_base_url().'templates/'.Application::Get('template').'/images/instagram.png" width="40" alt="Instagram" class="CToWUd"></a>&nbsp;
                                </span>
                                <br>
                              </span>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div style="background-color:#DDDDDD;width:100%;">
              <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#DDDDDD" style="background-color:#DDDDDD;border-collapse:collapse;">
                <tbody>
                  <tr>
                    <td bgcolor="#DDDDDD" align="center">
                      <table width="100%" cellspacing="0" cellpadding="0" border="0" bgcolor="#ffffff" align="center" style="border-collapse:collapse;max-width:600px;" class="m_-7210621922111406428responsive-table">
                        <tbody>
                          <tr>
                            <td width="100%" bgcolor="#d8a063" style="text-align:center;background-color:#d8a063;padding-bottom:20px;width:100%;color:#e0e0e0;font-family:\"Proxima nova\", sans-serif;font-size:13px;">
                              	www.hotel-booking-script.com
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </td>
        </tr>
      </table>
    </center>
  </body>
</html>
';

return $content;
