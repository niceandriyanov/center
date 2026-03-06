<?php
/**
 * Шаблон письма пользователю: оплата прошла успешно.
 *
 * @package center-med-renovatio
 */

defined( 'ABSPATH' ) || exit;

$email_data = isset( $email_data ) && is_array( $email_data ) ? $email_data : [];

$user_name            = isset( $email_data['user_name'] ) ? sanitize_text_field( (string) $email_data['user_name'] ) : '';
$appointment_datetime = isset( $email_data['appointment_datetime'] ) ? sanitize_text_field( (string) $email_data['appointment_datetime'] ) : '';
$specialist_name      = isset( $email_data['specialist_name'] ) ? sanitize_text_field( (string) $email_data['specialist_name'] ) : '';
$home_url             = isset( $email_data['home_url'] ) ? esc_url( (string) $email_data['home_url'] ) : esc_url( home_url( '/' ) );
$manager_url          = isset( $email_data['manager_url'] ) ? esc_url( (string) $email_data['manager_url'] ) : esc_url( 'https://t.me/handlingbetter' );
$telegram_channel_url = isset( $email_data['telegram_channel_url'] ) ? esc_url( (string) $email_data['telegram_channel_url'] ) : esc_url( 'https://t.me/handlingbettercenter' );
$logo_url             = isset( $email_data['logo_url'] ) ? esc_url( (string) $email_data['logo_url'] ) : '';

if ( '' === $logo_url && defined( 'CENTER_MED_RENOVATIO_PLUGIN_FILE' ) ) {
	$logo_url = esc_url( plugins_url( 'assets/images/svg_2_1.png', CENTER_MED_RENOVATIO_PLUGIN_FILE ) );
}

if ( '' === $user_name ) {
	$user_name = __( 'Клиент', 'center-med-renovatio' );
}

if ( '' === $appointment_datetime ) {
	$appointment_datetime = '-';
}

if ( '' === $specialist_name ) {
	$specialist_name = '-';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" lang="ru">
 <head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <meta name="x-apple-disable-message-reformatting">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta content="telephone=no" name="format-detection">
  <title>Ваша консультация забронирована и оплачена</title><!--[if (mso 16)]>
    <style type="text/css">
    a {text-decoration: none;}
    </style>
    <![endif]--><!--[if gte mso 9]><style>sup { font-size: 100% !important; }</style><![endif]--><!--[if gte mso 9]>
<noscript>
         <xml>
           <o:OfficeDocumentSettings>
           <o:AllowPNG></o:AllowPNG>
           <o:PixelsPerInch>96</o:PixelsPerInch>
           </o:OfficeDocumentSettings>
         </xml>
      </noscript>
<![endif]--><!--[if mso]><xml>
    <w:WordDocument xmlns:w="urn:schemas-microsoft-com:office:word">
      <w:DontUseAdvancedTypographyReadingMail/>
    </w:WordDocument>
    </xml><![endif]-->
  <style type="text/css">.rollover:hover .rollover-first {
  max-height:0px!important;
  display:none!important;
}
.rollover:hover .rollover-second {
  max-height:none!important;
  display:block!important;
}
.rollover span {
  font-size:0px;
}
u + .body img ~ div div {
  display:none;
}
#outlook a {
  padding:0;
}
span.MsoHyperlink,
span.MsoHyperlinkFollowed {
  color:inherit;
  mso-style-priority:99;
}
a.h {
  mso-style-priority:100!important;
  text-decoration:none!important;
}
a[x-apple-data-detectors],
#MessageViewBody a {
  color:inherit!important;
  text-decoration:none!important;
  font-size:inherit!important;
  font-family:inherit!important;
  font-weight:inherit!important;
  line-height:inherit!important;
}
.p {
  display:none;
  float:left;
  overflow:hidden;
  width:0;
  max-height:0;
  line-height:0;
  mso-hide:all;
}
@media only screen and (max-width:600px) {.bq { padding-right:0px!important } .bp { padding-left:0px!important }  *[class="gmail-fix"] { display:none!important } p, a { line-height:150%!important } h1, h1 a { line-height:120%!important } h2, h2 a { line-height:120%!important } h3, h3 a { line-height:120%!important } h4, h4 a { line-height:120%!important } h5, h5 a { line-height:120%!important } h6, h6 a { line-height:120%!important }  .bm p { }   h1 { font-size:36px!important; text-align:left } h2 { font-size:26px!important; text-align:left } h3 { font-size:20px!important; text-align:left } h4 { font-size:24px!important; text-align:left } h5 { font-size:20px!important; text-align:left } h6 { font-size:16px!important; text-align:left }        .bn p, .bn a { font-size:14px!important } .bm p, .bm a { font-size:16px!important }      .bi, .bi h1, .bi h2, .bi h3, .bi h4, .bi h5, .bi h6 { text-align:left!important }  .bg .rollover:hover .rollover-second, .bh .rollover:hover .rollover-second, .bi .rollover:hover .rollover-second { display:inline!important }   a.h, button.h, label.h { font-size:20px!important; padding:10px 20px 10px 20px!important; line-height:120%!important } a.h, button.h, label.h, .be { display:inline-block!important } .bc, .bc.bd, .bc .h { display:block!important }   .u table, .v table, .w table, .u, .w, .v { width:100%!important; max-width:600px!important } .adapt-img { width:100%!important; height:auto!important }           .h-auto { height:auto!important } .img-1496 { width:250px!important; height:auto!important }  .j .k, .j .k * { font-size:20px!important; line-height:150%!important } a.h.i { font-size:16px!important } .g .d, .g .d * { line-height:150%!important; font-size:16px!important } .f .d, .f .d * { line-height:150%!important; font-size:16px!important } .e .d, .e .d * { font-size:16px!important } .c .d, .c .d * { font-size:16px!important } .a .b, .a .b * { font-size:18px!important } }
@media screen and (max-width:384px) {.mail-message-content { width:414px!important } }</style>
 </head>
 <body class="body" style="width:100%;height:100%;font-family:arial, 'helvetica neue', helvetica, sans-serif;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;padding:0;Margin:0">
  <div dir="ltr" class="es-wrapper-color" lang="ru" style="background-color:#FAFAFA"><!--[if gte mso 9]>
			<v:background xmlns:v="urn:schemas-microsoft-com:vml" fill="t">
				<v:fill type="tile" color="#fafafa"></v:fill>
			</v:background>
		<![endif]-->
   <table width="100%" cellspacing="0" cellpadding="0" class="es-wrapper" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px;padding:0;Margin:0;width:100%;height:100%;background-repeat:repeat;background-position:center top;background-color:#FAFAFA">
     <tr>
      <td valign="top" style="padding:0;Margin:0">
       <table cellpadding="0" cellspacing="0" align="center" class="v" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px;width:100%;table-layout:fixed !important;background-color:transparent;background-repeat:repeat;background-position:center top">
         <tr>
          <td align="center" style="padding:0;Margin:0">
           <table bgcolor="#ffffff" align="center" cellpadding="0" cellspacing="0" class="bn" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px;background-color:transparent;width:600px">
             <tr>
              <td align="left" style="Margin:0;padding-top:10px;padding-right:20px;padding-bottom:10px;padding-left:20px">
               <table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px">
                 <tr>
                  <td valign="top" align="center" class="bq" style="padding:0;Margin:0;width:560px">
                   <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px">
                     <tr>
                      <td align="center" style="padding:0;Margin:0;font-size:0px"><a target="_blank" href="<?php echo esc_url( $home_url ); ?>" style="mso-line-height-rule:exactly;text-decoration:underline;color:#666666;font-size:14px"><img src="<?php echo esc_url( $logo_url ); ?>" width="250" title="Logo" class="img-1496" style="display:block;font-size:12px;border:0;outline:none;text-decoration:none;margin:0" height="45" alt=""></a></td>
                     </tr>
                   </table></td>
                 </tr>
               </table></td>
             </tr>
           </table></td>
         </tr>
       </table>
       <table cellpadding="0" cellspacing="0" align="center" class="u" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px;width:100%;table-layout:fixed !important">
         <tr>
          <td align="center" style="padding:0;Margin:0">
           <table bgcolor="#ffffff" align="center" cellpadding="0" cellspacing="0" class="bm" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px;background-color:#FFFFFF;width:600px">
             <tr>
              <td align="left" style="Margin:0;padding-top:10px;padding-right:20px;padding-bottom:10px;padding-left:20px">
               <table cellpadding="0" cellspacing="0" width="100%" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px">
                 <tr>
                  <td align="center" valign="top" style="padding:0;Margin:0;width:560px">
                   <table cellpadding="0" cellspacing="0" width="100%" role="presentation" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px">
                     <tr>
                      <td align="center" class="j" style="padding:0;Margin:0;padding-top:10px;padding-bottom:10px"><h1 class="k bi" style="Margin:0;font-family:arial, 'helvetica neue', helvetica, sans-serif;mso-line-height-rule:exactly;letter-spacing:0;font-size:20px;font-style:normal;font-weight:bold;line-height:20px;color:#333333">Спасибо за выбор нашего центра!</h1></td>
                     </tr>
                     <tr>
                      <td align="left" class="bq bp e" style="Margin:0;padding-top:5px;padding-right:40px;padding-bottom:15px;padding-left:5px"><p class="d" style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:24px;letter-spacing:0;color:#333333;font-size:16px"><?php echo esc_html( $user_name ); ?>, ваша консультация успешно забронирована и оплачена.</p><p class="d" style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:24px;letter-spacing:0;color:#333333;font-size:16px"><br></p><p class="d" style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:24px;letter-spacing:0;color:#333333;font-size:16px"><strong style="font-weight:700 !important">Дата и время:</strong> <?php echo esc_html( $appointment_datetime ); ?></p><p class="d" style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:24px;letter-spacing:0;color:#333333;font-size:16px"><strong style="font-weight:700 !important">Специалист</strong>: <?php echo esc_html( $specialist_name ); ?><br></p><p class="d" style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:24px;letter-spacing:0;color:#333333;font-size:16px"><br></p><p class="d" style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:24px;letter-spacing:0;color:#333333;font-size:16px">Специалист направит вам ссылку на видеозвонок<strong style="font-weight:700 !important"> не позднее чем за час до начала встречи.</strong></p></td>
                     </tr>
                     <tr>
                      <td align="center" bgcolor="#f6f5f5" class="a" style="padding:0;Margin:0;padding-left:5px;padding-top:15px;padding-bottom:5px"><p class="b" style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:27px;letter-spacing:0;color:#333333;font-size:18px"><strong style="font-weight:700 !important">Про перенос и отмену</strong></p></td>
                     </tr>
                     <tr>
                      <td align="left" bgcolor="#f6f5f5" class="c" style="padding:0;Margin:0;padding-top:5px;padding-bottom:15px;padding-left:5px"><p class="d" style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:24px;letter-spacing:0;color:#333333;font-size:16px">Мы не возвращаем оплату, если консультация отменена менее чем за 48 часов до начала. Если вы хотите перенести время — свяжитесь с администратором, и мы вам поможем!</p></td>
                     </tr>
                     <tr>
                      <td align="center" style="padding:0;Margin:0;padding-bottom:10px;padding-top:20px"><span class="be bc" style="border-style:solid;border-color:#2CB543;background:#366159;border-width:0px;display:block;border-radius:44px;width:auto"><a href="<?php echo esc_url( $manager_url ); ?>" target="_blank" class="h i" style="mso-style-priority:100 !important;text-decoration:none !important;mso-line-height-rule:exactly;color:#FFFFFF;font-size:16px;padding:15px 30px 10px;display:block;background:#366159;border-radius:44px;font-family:arial, 'helvetica neue', helvetica, sans-serif;font-weight:normal;font-style:normal;line-height:19.2px;width:auto;text-align:center;letter-spacing:0;mso-padding-alt:0;mso-border-alt:10px solid #366159;padding-left:5px;padding-right:5px">Связаться с менеджером</a></span></td>
                     </tr>
                     <tr>
                      <td align="left" class="g" style="padding:0;Margin:0;padding-top:10px;padding-bottom:5px"><p class="es-override-size d" style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:24px;letter-spacing:0;color:#333333;font-size:16px">Также мы выпускаем видео, подкасты и статьи на тему ментального здоровья. Подпишитесь на наш <a target="_blank" href="<?php echo esc_url( $telegram_channel_url ); ?>" style="mso-line-height-rule:exactly;text-decoration:underline;color:#366159;font-size:16px">Telegram-канал</a>, чтобы ничего не пропустить.</p></td>
                     </tr>
                   </table></td>
                 </tr>
               </table></td>
             </tr>
             <tr>
              <td align="left" bgcolor="#fafafa" style="Margin:0;padding-top:10px;padding-right:20px;padding-bottom:10px;padding-left:20px;background-color:#fafafa">
               <table width="100%" cellpadding="0" cellspacing="0" role="none" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px">
                 <tr>
                  <td align="left" style="padding:0;Margin:0;width:560px">
                   <table cellspacing="0" width="100%" role="presentation" cellpadding="0" style="mso-table-lspace:0pt;mso-table-rspace:0pt;border-spacing:0px">
                     <tr>
                      <td align="left" class="f" style="padding:0;Margin:0"><p class="es-override-size d" style="Margin:0;mso-line-height-rule:exactly;font-family:arial, 'helvetica neue', helvetica, sans-serif;line-height:24px;letter-spacing:0;color:#333333;font-size:16px">С уважением,<br>Команда Справиться Проще</p></td>
                     </tr>
                   </table></td>
                 </tr>
               </table></td>
             </tr>
           </table></td>
         </tr>
       </table></td>
     </tr>
   </table>
  </div>
 </body>
</html>