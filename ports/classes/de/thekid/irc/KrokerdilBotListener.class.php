<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'peer.irc.IRCConnectionListener', 
    'peer.irc.IRCColor',
    'org.dict.DictClient',
    'text.translator.Swabian',
    'peer.Socket',
    'io.File'
  );

  /**
   * Krokerdil Bot
   *
   * @see      xp://peer.irc.IRCConnectionListener
   * @purpose  IRC Bot
   */
  class KrokerdilBotListener extends IRCConnectionListener {
    var
      $tstart       = 0,
      $config       = NULL,
      $lists        = array(),
      $karma        = array(),
      $recognition  = array(),
      $dictc        = NULL,
      $quote        = NULL;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   &util.Properties config
     */
    function __construct(&$config) {
      $this->config= &$config;
      $this->reloadConfiguration();
      $this->tstart= time();

      // Set up DictClient
      $this->dictc= &new DictClient();
      
      // Set up quote client
      $this->quote= &new Socket('ausredenkalender.informatik.uni-bremen.de', 17);
      
      $l= &Logger::getInstance();
      $this->cat= &$l->getCategory();
      $this->dictc->setTrace($this->cat);
    }
    
    /**
     * Reload Bot configuration
     *
     * @access  protected
     */
    function reloadConfiguration() {
      $this->config->reset();
      $this->lists= array();
      
      // Set base directory for lists relative to that of the config file's
      $base= dirname($this->config->getFilename()).DIRECTORY_SEPARATOR;
      
      // Read word/message lists
      foreach ($this->config->readSection('lists') as $identifier => $file) {
        $this->lists[$identifier]= array();
        $f= &new File($base.$file);
        try(); {
          if ($f->open(FILE_MODE_READ)) while (($line= $f->readLine()) && !$f->eof()) {
            $this->lists[$identifier][]= $line;
          }
          $f->close();
        } if (catch('IOException', $e)) {
          $e->printStackTrace();
          return FALSE;
        }
      }
      
      // Read karma recognition phrases
      $f= &new File($base.$this->config->readString('karma', 'recognition'));
      try(); {
        if ($f->open(FILE_MODE_READ)) while (($line= $f->readLine()) && !$f->eof()) {
          list($pattern, $delta)= explode(':', $line);
          $this->recognition[$pattern]= $delta;
        }
        $f->close();
      } if (catch('IOException', $e)) {
        $e->printStackTrace();
        return FALSE;
      }
    }
    
    /**
     * Sends to a target, constructing it from a random element within a specified
     * list.
     *
     * @access  private
     * @param   &peer.irc.IRCConnection connection
     * @param   string target
     * @param   string list list identifier
     * @param   string nick
     * @param   string message
     * @return  bool success
     */
    function sendRandomMessage(&$connection, $target, $list, $nick, $message) {
      $format= $this->lists[$list][rand(0, sizeof($this->lists[$list])- 1)];
      if (empty($format)) return;
      
      if ('/me' == substr($format, 0, 3)) {
        $r= $connection->sendAction(
          $target, 
          substr($format, 4),
          $nick,
          $channel,
          $message
        );
      } else {
        $r= $connection->sendMessage(
          $target, 
          $format,
          $nick,
          $channel,
          $message
        );
      }
      return $r;
    }
    
    /**
     * Helper method for privileged actions.
     *
     * @access  protected
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick
     * @param   string password
     * @return  bool
     */
    function doPrivileged(&$connection, $nick, $password) {
      if ($this->config->readString('control', 'password') == $password) return TRUE;
      
      $connection->sendMessage($nick, 'Nice try, but >%s< is incorrect', $params);
      return FALSE;
    }
    
    /**
     * Helper method to set karma for a nick. Also handles karma floods
     *
     * @access  protected
     * @param   string nick
     * @param   int delta
     * @param   string reason default NULL
     */
    function setKarma($nick, $delta, $reason= NULL) {
      static $last= array();
      
      if (!isset($this->karma[$nick])) {
        $this->karma[$nick]= 0; // Neutral
      }
      
      if ($reason && isset($last[$nick][$reason]) && (time() - $last[$nick][$reason] <= 2)) {
        $this->cat && $this->cat->warnf(
          'Karma flood from %s (last karma for %s set at %s)', 
          $nick,
          $reason,
          date('r', $last[$nick][$reason])
        );
        $this->karma[$nick]-= 10;
      } else {
        $this->karma[$nick]+= $delta;
        $last[$nick]= array();
      }

      $this->cat && $this->cat->debugf(
        'Changing karma for %s by %d because of %s (total: %d)', 
        $nick,
        $delta,
        $reason,
        $this->karma[$nick]
      );
      $last[$nick][$reason]= time();
    }
    

    /**
     * Callback for nick changes
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string channel
     * @param   string nick the old nick
     * @param   string new the new nick
     */
    function onNickChanges(&$connection, $channel, $nick, $new) {
      $this->setKarma($new, $this->karma[$nick]);
    }
    
    /**
     * Callback for private messages
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick
     * @param   string target
     * @param   string message
     */
    function onPrivateMessage(&$connection, $nick, $target, $message) {
      
      // Commands
      if (sscanf($message, "!%s %[^\r]", $command, $params)) {
        switch (strtolower($command)) {
          case '@reload':
            if ($this->doPrivileged($connection, $nick, $params)) {
              $this->reloadConfiguration();
              $connection->sendAction($nick, 'received SIGHUP and reloads his configuration');
            }
            break;
          
          case '@changenick':
            list($new_nick, $password)= explode(' ', $params);
            if ($this->doPrivileged($connection, $nick, $password)) {
              $connection->writeln('NICK %s', $params);
            }
            break;
          
          case '@karma':
            if ($this->doPrivileged($connection, $nick, $params)) {
              foreach ($this->karma as $name => $value) {
                $connection->sendMessage(
                  $nick,
                  '%s: %d', 
                  $name,
                  $value
                );
              }
            }
            break;
          
          case 'karma':
            $this->setKarma($nick, 0);  // Make sure array is initialized
            $connection->sendMessage($target, 'Karma f�r %s: %d', $nick, $this->karma[$nick]);
            break;
          
          case 'uptime':
            $delta= time() - $this->tstart;
            
            // Break it up into days, hours and minutes
            $days= $delta / 86400;
            $delta-= (int)$days * 86400;
            $hours= $delta / 3600;
            $delta-= (int)$hours * 3600;
            $minutes= $delta / 60;
            
            $connection->writeln(
              'NOTICE %s :Uptime: %d Tag(e), %d Stunde(n) und %d Minute(n)',
              $target, $days, $hours, $minutes
            );

            $this->setKarma($nick, 1, '@@uptime');
            break;

          case 'quote':
            try(); {
              $this->quote->connect();
              do {
                if (!($buf= $this->quote->readLine())) continue;
                $connection->sendMessage(
                  $target, 
                  '%s%s', 
                  IRCColor::forCode(IRC_COLOR_YELLOW), 
                  $buf
                );
              } while (!$this->quote->eof());
              $this->quote->close();
            } if (catch('IOException', $e)) {
              $e->printStackTrace();
              $connection->sendMessage($target, '!%s', $e->getMessage());
              break;
            }
            break;              
            
          case 'whatis':
            try(); {
              $this->dictc->connect('dict.org', 2628);
              $definitions= $this->dictc->getDefinition($params, '*');
              $this->dictc->close();
            } if (catch('Exception', $e)) {
              $e->printStackTrace();
              $connection->sendMessage($target, '!%s', $e->getMessage());
              break;
            }
            
            // Check if we found something
            if (empty($definitions)) {
              $connection->sendMessage(
                $target, 
                '"%s": No match found', 
                $params
              );
              break;
            }
            
            // Make definitions available via www
            $file= &new File(sprintf(
              '%s%swhatis_%s.html',
              rtrim($this->config->readString('whatis_www', 'document_root'), DIRECTORY_SEPARATOR),
              DIRECTORY_SEPARATOR,
              strtolower(preg_replace('/[^a-z0-9_]/i', '_', $params))
            ));
            try(); {
              $file->open(FILE_MODE_WRITE);
              $file->write('<h1>What is "'.$params.'"?</h1>');
              $file->write('<ol>');
              for ($i= 0, $s= sizeof($definitions); $i < $s; $i++) {
                $file->write('<li>Definition: '.$definitions[$i]->getDatabase().')<br/>');
                $file->write('<pre>'.$definitions[$i]->getDefinition().'</pre>');
                $file->write('</li>');
              }
              $file->write('</ol>');
              $file->write('<hr/><small>Generated by '.$connection->user->getNick().' at '.date('r').'</small>');
              $file->close();
            } if (catch('IOException', $e)) {
              $e->printStackTrace();
              $connection->sendMessage(
                $target, 
                '- %s', 
                $e->getMessage()
              );
              break;
            }
            $connection->sendMessage(
              $target, 
              '"%s": Definitions @ %s%s', 
              $params,
              $this->config->readString('whatis_www', 'base_href'),
              urlencode($file->getFileName())
            );
            break;
          
          case 'say':
            list($dest, $message)= explode(' ', $params, 2);
            $connection->sendMessage($dest, $message);
            break;

          case 'do':
            list($dest, $action)= explode(' ', $params, 2);
            $connection->sendAction($dest, $action);
            break;
          
          case 'schwob':
            $connection->sendMessage($target, Swabian::translate($params));
            break;

          case 'bite':
            $connection->sendAction($target, 'bei�t %s', $params);
            break;
            
          case 'beep':
          case 'hup':
            $connection->sendAction($target, 'hupt (%s)'."\7", $params);
            break;
          
          case 'falsch':
            $connection->sendMessage(
              $target, 
              '%s ist zwar s��, ABER %sFALSCH!', 
              $params, 
              IRCColor::forCode(IRC_COLOR_RED)
            );
            break;
        
          case 'maul':
            $this->sendRandomMessage($connection, $target, 'shutup', $params, NULL);
            break;
          
          case 'i':
          case 'idiot':
            if ('#' == $params{0}) {    // Allow #<channel>/<nick> so private messages work
              list($target, $params)= explode('/', $params);
            }
            
            // Don't insult yourself - instead, insult the user:) Check on similar text
            // so people can't get away with misspelling the name. We might accidentally
            // also insult users with similar names than ours, but, hey, their fault.
            similar_text(strtolower($params), strtolower($connection->user->getNick()), $percent);
            if ($percent >= 75) {
              $params= $nick;
              $format= '%s, du bist %s';
              $this->setKarma($nick, -5, '@@idiot');
              $connection->sendAction($target, 'beleidigt sich nicht selbst');
            } else {
              $format= '%s ist %s';
            }
            
            $connection->sendMessage(
              $target, 
              $format, 
              $params, 
              $this->lists['swears'][rand(0, sizeof($this->lists['swears'])- 1)]
            );
            break;

          case 'i+':
          case 'idiot+':
            if (in_array($params, $this->lists['swears'])) {
              $connection->sendAction(
                $target, 
                'kannte das Schimpfwort >%s%s%s< schon', 
                IRCColor::forCode(IRC_COLOR_ORANGE),
                $params, 
                IRCColor::forCode(IRC_COLOR_DEFAULT)
              );
              break;                          
            }
            
            // Update swears array
            $this->lists['swears'][]= $params;
            
            // Also update the swears file
            $f= &new File(sprintf(
              '%s%s%s',
              dirname($this->config->getFilename()),
              DIRECTORY_SEPARATOR,
              $this->config->readString('lists', 'swears')
            ));
            try(); {
              $f->open(FILE_MODE_APPEND);
              $f->write($params."\n");
              $f->close();
            } if (catch('IOException', $e)) {
              $connection->sendMessage($target, '! '.$e->getMessage());
              break;
            }
            $connection->sendAction($target, 'hat jetzt %d Schimpfw�rter', sizeof($this->lists['swears']));
            break;              

          case 'ascii':
            $connection->sendMessage($target, 'ASCII #%d = %s', $params, chr($params));
            break;
        }
        return;
      }
      
      // Any other phrase containing my name
      if (stristr($message, $connection->user->getNick())) {
        $this->sendRandomMessage($connection, $target, 'talkback', $nick, $message);

        // See if we can recognize something here and calculate karma - multiplied
        // by four because this message is directed at me.
        foreach ($this->recognition as $pattern => $delta) {
          if (!preg_match($pattern, $message)) continue;
          $this->setKarma($nick, $delta * 4, $pattern);
        }
        return;
      }
      
      // Produce random noise
      switch (rand(0, 30)) {
        case 15: 
          $this->sendRandomMessage($connection, $target, 'noise', $nick, $message);
          break;
        
        case 16:
          $this->sendRandomMessage(
            $connection, 
            $target, 
            $this->karma[array_rand($this->karma)] < 0 ? 'karma.dislike' : 'karma.like', 
            $nick, 
            $message
          );
          break;
      }

      // Karma recognition
      foreach ($this->recognition as $pattern => $delta) {
        if (!preg_match($pattern, $message)) continue;
        $this->setKarma($nick, $delta, $pattern);
      }
    }

    /**
     * Callback for server message REPLY_ENDOFMOTD (376)
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string server
     * @param   string target whom the message is for
     * @param   string data
     */
    function onEndOfMOTD(&$connection, $server, $target, $data) {
      if ($this->config->hasSection('autojoin')) {
        $connection->join(
          $this->config->readString('autojoin', 'channel'),
          $this->config->readString('autojoin', 'password', NULL)
        );
      }
    }    

    /**
     * Callback for invitations
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick sending the invitation
     * @param   string who who is invited
     * @param   string channel invitation is for
     */
    function onInvite(&$connection, $nick, $who, $channel) {
      if ($this->config->readBool('invitations', 'follow', FALSE)) {
        $connection->join($channel);
      }
    }
  
    /**
     * Callback for kicks
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string channel the channel the user was kicked from
     * @param   string nick that initiated the kick
     * @param   string who who was kicked
     * @param   string reason what reason the user was kicked for
     */
    function onKicks(&$connection, $channel, $nick, $who, $reason) {
      if (strcasecmp($who, $connection->user->getNick()) == 0) {
        $connection->join($channel);
        $connection->sendMessage($nick, 'He! "%s" ist KEIN Grund', $reason);
        $connection->sendAction($channel, '%s kickt arme unschuldige Bots, und das wegen so etwas lumpigem wie %s', $nick, $reason);

        $this->setKarma($nick, -10, '@@kick');
      }
    }
  
    /**
     * Callback for joins
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string channel which channel was joined
     * @param   string nick who joined
     */
    function onJoins(&$connection, $channel, $nick) {
      if (strcasecmp($nick, $connection->user->getNick()) == 0) {
        $connection->writeln('NOTICE %s :%s is back!', $channel, $nick);
      } else {
        $this->sendRandomMessage($connection, $channel, 'join', $nick, NULL);
      }
    }

    /**
     * Callback for parts
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string channel which channel was part
     * @param   string nick who part
     * @param   string message the part message, if any
     */
    function onParts(&$connection, $channel, $nick, $message) {
      $this->sendRandomMessage($connection, $channel, 'leave', $nick, $message);
    }

    /**
     * Callback for actions. Actions are when somebody writes /me ...
     * in their IRC window.
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick who initiated the action
     * @param   string target where action was initiated
     * @param   string action what actually happened (e.g. "looks around")
     */
    function onAction(&$connection, $nick, $target, $params) {
      if (10 == rand(0, 20)) {
        $connection->sendAction($target, 'macht %s nach und %s auch', $nick, $params);
        $this->setKarma($nick, 1, '@@imitate');
      }

      // Karma recognition
      foreach ($this->recognition as $pattern => $delta) {
        if (!preg_match($pattern, $message)) continue;
        $this->setKarma($nick, $delta, $pattern);
      }
    }
    
    /**
     * Callback for CTCP VERSION
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick nick requesting version
     * @param   string target where version was requested
     * @param   string params additional parameters
     */
    function onVersion(&$connection, $nick, $target, $params) {
      $connection->writeln('NOTICE %s :%sVERSION Krokerdil $Revision$%s', $nick, "\1", "\1");
    }
  }
?>
