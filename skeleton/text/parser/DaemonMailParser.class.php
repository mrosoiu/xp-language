<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('peer.mail.Message');

  /**
   * Mailer-Daemon failure notification parser
   *
   * <code>
   *   $p= &new DaemonMailParser();                           
   *
   *   // Header handling                                     
   *   $p->addHeaderFound(                                    
   *     'X-Message-BackReference',                           
   *     $f= 'var_dump'                                           
   *   );                                                     
   *   $p->addHeaderMatch(                                    
   *     'X-Message-BackReference',                           
   *     '/^([0-9]+)@@kk\.?(test)?([0-9]+)\.([0-9]+)$/',      
   *     $f= 'var_dump'                                           
   *   );                                                     
   *
   *   try(); {                                               
   *     $p->parse($message);                                 
   *   } if (catch('FormatException', $e)) {                  
   *                                                          
   *     // This does not seem to be a Mailer Daemon Message  
   *     $e->printStackTrace();                               
   *     exit(-1);                                            
   *   } if (catch('Exception', $e)) {                        
   *
   *     // Any other error                                   
   *     $e->printStackTrace();                               
   *     exit(-2);                                            
   *   }                                                      
   * </code> 
   *
   * @purpose  DaemonMail Parser
   */
  class DaemonMailParser extends Object {
    var
      $_hcb = array();
      
      
    /**
     * Set handler for the event that a header is found
     *
     * @access  public
     * @param   string element
     * @param   &function func
     */
    function addHeaderFound($header, &$func) {
      $this->_hcb['found_'.$header]= array(NULL, &$func);
    }

    /**
     * Set handler for the event that a header matches a specified regular expression
     *
     * @access  public
     * @param   string element
     * @param   string regex
     * @param   &function func
     */
    function addHeaderMatch($header, $regex, &$func) {
      $this->_hcb['match_'.$header]= array($regex, &$func);
    }
    
    /**
     * Parse a stream
     *
     * @access  public 
     * @param   &peer.mail.Message
     * @return  bool success
     * @throws  FormatException
     * @throws  IllegalArgumentException
     */
    function parse(&$message) {
      if (!is_a($message, 'Message')) {
        trigger_error('Type: '.get_class($message), E_USER_NOTICE);
        return throw(new IllegalArgumentException('Parameter message is not peer.mail.Message object'));
      }
      
      var_dump('FROM', $message->getFrom());
      var_dump('DATE', $message->date->toString());
      
      // First, look in the headers

      // "In-Reply-To": These are stupid autoresponders or people replying 
      // to an address they shouldn't be.
      if (NULL !== ($irt= $message->getHeader('In-Reply-To'))) {
        trigger_error('Message is in reply to: '.$irt, E_USER_NOTICE);
        return throw(new FormatException('Message has In-Reply-To header, Mailer Daemons do not set these'));
      }
      
      // Is there a header named "X-Failed-Recipients"?
      if (NULL !== ($rcpt= $message->getHeader('X-Failed-Recipients'))) {
        var_dump('FAILED_RECIPIENT', $rcpt);
      }
      
      // If this is a multipart message, try and seperate parts:
      // =======================================================
      // [-- Attachment #1 --]
      // [-- Type: text/plain, Encoding: 7bit, Size: 1.0K --]
      // 
      // The original message was received at Mon, 17 Feb 2003 04:08:12 -0500 (EST)
      // from moutng.kundenserver.de [212.227.126.189]
      // 
      // 
      // *** ATTENTION ***
      // 
      // Your e-mail is being returned to you because there was a problem with its
      // delivery.  The address which was undeliverable is listed in the section
      // labeled: "----- The following addresses had permanent fatal errors -----".
      // 
      // The reason your mail is being returned to you is listed in the section
      // labeled: "----- Transcript of Session Follows -----".
      // 
      // The line beginning with "<<<" describes the specific reason your e-mail could
      // not be delivered.  The next line contains a second error message which is a
      // general translation for other e-mail servers.
      // 
      // Please direct further questions regarding this message to your e-mail
      // administrator.
      // 
      // --AOL Postmaster
      // 
      // 
      // 
      //    ----- The following addresses had permanent fatal errors -----
      // <uliruedi@aol.com>
      // 
      //    ----- Transcript of session follows -----
      // ... while talking to air-xn02.mail.aol.com.:
      // >>> RCPT To:<uliruedi@aol.com>
      // <<< 550 MAILBOX NOT FOUND
      // 550 <uliruedi@aol.com>... User unknown
      // 
      // [-- Attachment #2 --]
      // [-- Type: message/delivery-status, Encoding: 7bit, Size: 0.3K --]
      // Content-Type: message/delivery-status
      // 
      // Reporting-MTA: dns; rly-xn01.mx.aol.com
      // Arrival-Date: Mon, 17 Feb 2003 04:08:12 -0500 (EST)
      // 
      // Final-Recipient: RFC822; uliruedi@aol.com
      // Action: failed
      // Status: 2.0.0
      // Remote-MTA: DNS; air-xn02.mail.aol.com
      // Diagnostic-Code: SMTP; 250 OK
      // Last-Attempt-Date: Mon, 17 Feb 2003 04:08:27 -0500 (EST)
      // 
      // [-- Attachment #3 --]
      // [-- Type: message/rfc822, Encoding: 7bit, Size: 4.1K --]
      // Content-Type: message/rfc822
      // 
      // Received: from  moutng.kundenserver.de (moutng.kundenserver.de [212.227.126.189]) by
      // +rly-xn01.mx.aol.com (v90_r2.6) with ESMTP id MAILRELAYINXN19-0217040812; Mon, 17 Feb 2003
      // +04:08:12 -0500
      // Received: from [212.227.126.159] (helo=mxng09.kundenserver.de)
      //         by moutng.kundenserver.de with esmtp (Exim 3.35 #1)
      //         id 18khFz-0000pQ-00
      //         for UliRuedi@aol.com; Mon, 17 Feb 2003 10:08:11 +0100
      // Received: from [172.19.1.25] (helo=newsletter.kundenserver.de)
      //         by mxng09.kundenserver.de with esmtp (Exim 3.35 #1)
      //         id 18khFy-0003xv-00
      //         for UliRuedi@aol.com; Mon, 17 Feb 2003 10:08:10 +0100
      // Received: from newsletter by newsletter.kundenserver.de with local (Exim 3.35 #1)
      //         id 18khDT-0000YH-00
      //         for UliRuedi@aol.com; Mon, 17 Feb 2003 10:05:35 +0100
      // To: Herr Behrhof <UliRuedi@aol.com>
      // Subject: Wichtige Neuerung : Einf?hrung des 1&1 Kundenkennwortes f?r das 1&1 Control-Center
      // From: "1&1 Internet AG" <noreply@1und1.com>
      // X-Priority: 3
      // Content-Type: text/plain; charset=iso-8859-1
      // Message-ID: <NL12074.2946803@newsletter.kundenserver.de>
      // X-News-BackReference: 12074.6170236
      // X-Ignore: yes
      // X-Binford: 61000 (more power)
      // MIME-Version: 1.0
      // Content-Transfer-Encoding: 8bit
      // Date: Mon, 17 Feb 2003 10:05:35 +0100
      if (is_a($message, 'MimeMessage')) {
        $body= NULL;
        while ($part= &$message->getPart()) switch ($part->getContentType()) {
          case 'message/delivery-status':
            $state= DMP_HEADERS;
            var_dump('###'.$part->getBody().'###');
            break;

          case 'message/rfc822':
            var_dump('&&&&&&&&&&&&&&&&&&&&&&&&', $part);
            $body= $part->getHeaderString();
            break;

          default:
            var_dump('==='.$part->getContentType().'===');
            // Ignore
        }
      } else {
        $body= $message->getBody();
      }
      
      if (!($t= strtok($body, "\r\n"))) {
        trigger_error('Body: '.var_export($body, 1), E_USER_NOTICE);
        return throw(new FormatException('Tokenizing failed'));
      }
      
      // Loop through tokens
      do {
        # printf(">> %s\n", $t);
        
        // Sendmail
        // ========
        // ... while talking to mx01.kundenserver.de.:
        // >>> RCPT To:<avni@bilgin-online.de>
        // <<< 550 Cannot route to <avni@bilgin-online.de>
        // 550 5.1.1 Avni Bilgin <avni@bilgin-online.de>... User unknown
        if ('... while talking to' == substr($t, 0, 20)) {
          var_dump('SENDMAIL', $message->headers, $t);
          
          // Read six lines
          
          $state= DMP_HEADERS;
          continue;
        }

        // Exim
        // ====
        // This message was created automatically by mail delivery software (Exim).
        // A message that you sent could not be delivered to one or more of its
        // recipients. This is a permanent error. The following address(es) failed:
        //   webmaster@b-w-f.net
        //     SMTP error from remote mailer after RCPT TO:<webmaster@b-w-f.net>:
        //     host mx01.kundenserver.de [212.227.126.152]: 550 Cannot route to <webmaster@b-w-f.net>
        // ------ This is a copy of the message, including all the headers. ------
        if ('This message was created automatically by mail delivery software' == substr($t, 0, 64)) {
          var_dump('EXIM', $message->headers, $t);
          
          // Read six lines
          
          $state= DMP_HEADERS;
          continue;
        }
        
        // T-Online
        // ========
        // |------------------------- Failed addresses follow: ---------------------|
        // <roland.tusche.@t-online.de> ... unknown user / Teilnehmer existiert nicht
        // |------------------------- Message text follows: ------------------------|
        if ('|------------------------- Failed addresses follow:' == substr($t, 0, 51)) {
          var_dump('T-ONLINE', $message->headers, $t);
          
          // Read two lines
          
          $state= DMP_HEADERS;
          continue;
        }
        
        // Postfix
        // =======
        // Reporting-MTA: dns; cia.schlund.de
        // Arrival-Date: Sun, 12 May 2002 09:06:07 +0200
        // 
        // Final-Recipient: RFC822; avni@bilgin-online.de
        // Action: failed
        // Status: 5.1.1
        // Remote-MTA: DNS; mx01.kundenserver.de
        // Diagnostic-Code: SMTP; 550 Cannot route to <avni@bilgin-online.de>
        // Last-Attempt-Date: Sun, 12 May 2002 09:34:05 +0200
        if ('Final-Recipient: RFC822; ' == substr($t, 0, 25)) {
          var_dump('POSTFIX', $message->headers, $t);
          
          $state= DMP_HEADERS;
          continue;
        }
        
      } while ($t= strtok("\r\n"));
      
      if ($state != DMP_HEADERS) {
        echo "######################################################################################\n";
        var_dump($message->headers, $message->getBody());
      }
    }
  }
?>
