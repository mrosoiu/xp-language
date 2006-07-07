<?php
/* This class is part of the XP framework
 *
 * $Id$
 */

  uses('peer.server.Server', 'lang.RuntimeError');

  /**
   * Pre-Forking TCP/IP Server
   *
   * @ext      pcntl
   * @see      xp://peer.server.Server
   * @purpose  TCP/IP Server
   */
  class PreforkingServer extends Server {
    var
      $cat   = NULL,
      $count = 0;

    /**
     * Constructor
     *
     * @access  public
     * @param   string addr
     * @param   int port
     * @param   int count default 10 number of children to fork
     * @param   int maxrequests default 1000 maxmimum # of requests per child
     */
    function __construct($addr, $port, $count= 10, $maxrequests= 1000) {
      parent::__construct($addr, $port);
      $this->count= $count;
      $this->maxrequests= $maxrequests;
    }
    
    /**
     * Set a trace for debugging
     *
     * @access  public
     * @param   &util.log.LogCategory cat
     */
    function setTrace(&$cat) { 
      $this->cat= &$cat;
    }

    /**
     * Signal handler
     *
     * @access  protected
     * @param   int sig
     */
    function handleSignal($sig) {
      $this->cat && $this->cat->infof('Received signal %d in pid %d', $sig, getmypid());
      $this->terminate= TRUE;
    }

    /**
     * Service
     *
     * @access  public
     */
    function service() {
      if (!$this->socket->isConnected()) return FALSE;

      $children= array();
      $i= 0;
      $tcp= getprotobyname('tcp');
      while (!$this->terminate && ($i <= $this->count)) {
        $this->cat && $this->cat->debugf('Server #%d: Forking child %d', getmypid(), $i);
        $pid= pcntl_fork();
        if (-1 == $pid) {       // Woops?
          return throw(new RuntimeError('Could not fork'));
        } else if ($pid) {      // Parent
          $this->cat && $this->cat->infof('Server #%d: Forked child #%d with pid %d', getmypid(), $i, $pid);
          $children[$pid]= $i;
          $i++;
        } else {                // Child
          $requests= 0;
          while ($requests < $this->maxrequests) {
            try(); {
              $m= &$this->socket->accept();
            } if (catch('IOException', $e)) {
              $this->cat && $this->cat->warn('Child', getmypid(), 'in accept ~', $e);
              exit(-1);
            }
            if (!$m) continue;
            $this->tcpnodelay && $m->setOption($tcp, TCP_NODELAY, TRUE);
            $this->notify(new ConnectionEvent(EVENT_CONNECTED, $m));

            // Loop
            do {
              try(); {
                if (NULL === ($data= $m->readBinary())) break;
              } if (catch('IOException', $e)) {
                $this->notify(new ConnectionEvent(EVENT_ERROR, $m, $e));
                break;
              }

              // Notify listeners
              $this->notify(new ConnectionEvent(EVENT_DATA, $m, $data));

            } while (!$m->eof());

            $m->close();
            $this->notify(new ConnectionEvent(EVENT_DISCONNECTED, $m));
            $requests++;
            $this->cat && $this->cat->debug(
              'Child', getmypid(), 
              'requests=', $requests, 'max= ', $this->maxrequests
            );
          }

          // Exit out of child
          exit();
        }
        if ($i < $this->count) continue;

        // Set up signal handler so a kill -2 $pid (where $pid is the 
        // process id of the process we are running in) will cleanly shut
        // down this server. If this server is run within a thread (which
        // is recommended), a $thread->stop() will accomplish this.
        pcntl_signal(SIGINT, array(&$this, 'handleSignal'));
        
        // Wait until we are supposed to terminate. This condition variable
        // is set to TRUE by the signal handler. Sleep a second to decrease
        // load produced. Note: sleep() is interrupted by a SIGINT, we will
        // still be able to catch the shutdown signal in realtime.
        $this->cat && $this->cat->debug('Server #'.getmypid().': Starting main loop, children:', $children);
        while (!$this->terminate) { 
          sleep(1);
          if (($pid= pcntl_waitpid(-1, $status, WNOHANG)) <= 0) continue;
          
          // If, meanwhile, we've been interrupted, break out of both loops
          if ($this->terminate) break 2;

          // One of our children terminated, remove it from the process 
          // list and fork a new one
          $this->cat && $this->cat->warnf('Server #%d: Child %d died with exitcode %d', getmypid(), $pid, $status);
          unset($children[$pid]);
          $i--;
          break;
        }
        
        // Reset signal handler so it doesn't get copied to child processes
        pcntl_signal(SIGINT, SIG_DFL);
      }
      
      // Terminate children
      foreach ($children as $pid => $i) {
        $this->cat && $this->cat->infof('Server #%d: Terminating child #%d with pid %d', getmypid(), $i, $pid);
        posix_kill($pid, SIGINT);
        pcntl_waitpid($pid, $status, WUNTRACED);
        $this->cat && $this->cat->infof('Server #%d: Exitcode is %d', getmypid(), $status);
      }

      // Shut down ourselves
      $this->shutdown();
      $this->cat && $this->cat->infof('Server #%d: Shutdown complete', getmypid());
    }
  } implements(__FILE__, 'util.log.Traceable');
?>
