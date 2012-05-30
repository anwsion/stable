<?php
/*
+--------------------------------------------------------------------------
|   Anwsion [#RELEASE_VERSION#]
|   ========================================
|   by Tatfook Network Team
|   (c) 2011 - 2012 Anwsion Software
|   http://www.anwsion.com
|   ========================================
|   Support: zhengqiang@gmail.com
|   
+---------------------------------------------------------------------------
*/

class core_mail
{
	private $mail;
	private $mail_transport;
	private $charset = 'utf-8';
	private $queue = false;
	
	public function connect($queue = true, $email_type = '', $smtp_config = array())
	{
		$this->queue = $queue;
		
		if (!$email_type)
		{
			$email_type = get_setting('email_type');
		}
		
		if ($email_type == '1')
		{
			if(empty($smtp_config))
			{
				$auth = array(
						'auth' => 'login',
						'username' => get_setting('smtp_username'),
						'password' => get_setting('stmp_password')
				);
				
				$smtp_server = get_setting('smtp_server');
				
				if(intval(get_setting('smtp_port')) > 0)
				{
					$smtp_server .= ":" . get_setting('smtp_port');
				}
			}
			else
			{
				$auth = array(
						'auth' => 'login',
						'username' => $smtp_config['smtp_username'],
						'password' => $smtp_config['stmp_password'],
				);
				
				$smtp_server = $smtp_config['smtp_server'];
				
				if(intval($smtp_config['smtp_port']) > 0)
				{
					$smtp_server .= ":" . $smtp_config['smtp_port'];
				}
			}
			
			try 
			{
				$this->mail_transport = new Zend_Mail_Transport_Smtp($smtp_server, $auth);
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		else if ($email_type == '2')
		{
			try 
			{
				$this->mail_transport = new Zend_Mail_Transport_Sendmail(get_setting('from_email'));
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		
		return $this->mail_transport;
	}

	/**
	 * 
	 * @param  $from_email
	 * @param  $from_name
	 * @param  $to_email
	 * @param  $to_name
	 * @param  $title
	 * @param  $body
	 */
	public function send_mail($from_email, $from_name, $to_email, $to_name, $title, $body)
	{
		if(!$this->mail_transport)
		{
			$this->connect(false);
		}
		
		if (empty($from_email))
		{
			$from_email = get_setting('from_email');
		}
		
		if ($this->queue == false)
		{
			try
			{
				$mail = new Zend_Mail($this->charset);
				$mail->setBodyHtml($body); //可以发送HTML的邮件.真方便!
				//$mail->setBodyText($mailcontent);
				$mail->setFrom($from_email, $from_name);
				$mail->addTo($to_email, $to_name);
				$mail->setSubject($title);
				//$mail->setSubject("=?UTF-8?B?".base64_encode($title)."?=");
				$mail->send($this->mail_transport);
				return true;
			}
			catch (Exception $e)
			{
				return false;
			}
		}
		else
		{
			
			//	$body=str_replace("\r\n","",$body);//去回车

			$insert_arr["from_email"] = FORMAT::mysql_safe($from_email);
			$insert_arr["to_email"] = FORMAT::mysql_safe($to_email);
			$insert_arr["title"] = FORMAT::mysql_safe($title);
			$insert_arr["body"] = FORMAT::mysql_safe($body);
			$insert_arr["from_name"] = FORMAT::mysql_safe($from_name);
			$insert_arr["to_name"] = FORMAT::mysql_safe($to_name);
			$insert_arr["add_time"] = mktime();
			
			//处理回车
			GZ_APP::db()->insert(get_table("mail_queue"), $insert_arr);
			return GZ_APP::db()->lastInsertId();
		
		}
	
	}

}