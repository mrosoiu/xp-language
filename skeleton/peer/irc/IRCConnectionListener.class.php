<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  /**
   * Connection listener
   *
   * @see      xp://peer.irc.IRCConnection#addListener
   * @purpose  Abstract base class
   */
  class IRCConnectionListener extends Object {

    /**
     * Callback for Pings. Note that the PING has already been answered
     * when this method is called, so you won't have to send a PONG 
     * yourself.
     *
     * You might want to use this method in IRC-bots to accomplish the
     * task of deliberately being able to perform an action without any
     * other action having taken place (e.g., maintenance, reload config,
     * ...)
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string data
     */
    function onPings(&$connection, $data) { }

    /**
     * Callback for when a connection to the IRC server has been 
     * established. This method is called *after* a connecting was
     * successful.
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string server
     * @param   int port
     */
    function onConnect(&$connection, $server, $port) { }

    /**
     * Callback for when a connection to the IRC server has been 
     * closed. This method is called *before* the connection is actually
     * dropped; thus making it possible to say goodbye. You cannot do
     * anything to prevent disconnection, though.
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string server
     * @param   int port
     */
    function onDisconnect(&$connection, $server, $port) { }
    
    /**
     * Callback for server message MOTDSTART (375)
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string server
     * @param   string target whom the message is for
     * @param   string data
     */
    function onMOTDStart(&$connection, $server, $target, $data) { }

    /**
     * Callback for server message MOTD (372)
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string server
     * @param   string target whom the message is for
     * @param   string data
     */
    function onMOTD(&$connection, $server, $target, $data) { }

    /**
     * Callback for server message REPLY_ENDOFMOTD (376)
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string server
     * @param   string target whom the message is for
     * @param   string data
     */
    function onEndOfMOTD(&$connection, $server, $target, $data) { }
    
    /**
     * Callback for all other server messages
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string server
     * @param   int code one of the IRC_* constants from peer.irc.IRCConstants
     * @param   string target whom the message is for
     * @param   string data
     */
    function onServerMessage(&$connection, $server, $code, $target, $data) { }

    /**
     * Callback for invitations. Note: Due to the limitations of the INVITE
     * command, you won't be able to join password-protected channels unless
     * you know their password!
     *
     * Example: Join if we're invited:
     * <code>
     *   $connection->join($channel);
     * </code>
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick sending the invitation
     * @param   string who who is invited
     * @param   string channel invitation is for
     */
    function onInvite(&$connection, $nick, $who, $channel) { }

    /**
     * Callback for kicks
     *
     * Example (identifying being kicked):
     * <code>
     *   if (strcasecmp($who, $connection->user->getNick()) == 0) {
     *     // ... I was kicked ...
     *   }
     * </code>
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string channel the channel the user was kicked from
     * @param   string nick that initiated the kick
     * @param   string who who was kicked
     * @param   string reason what reason the user was kicked for
     */
    function onKicks(&$connection, $channel, $nick, $who, $reason) { }

    /**
     * Callback for quits
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string channel the channel the user quit from
     * @param   string nick who quit
     * @param   string reason what reason the user supplied for quitting
     */
    function onQuits(&$connection, $channel, $nick, $reason) { }

    /**
     * Callback for nick changes
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick the old nick
     * @param   string new the new nick
     */
    function onNickChanges(&$connection, $nick, $new) { }

    /**
     * Callback for joins
     *
     * Example (welcome users)
     * <code>
     *   // Send it to the channel so everybody knows
     *   $connection->sendMessage($channel, 'Welcome %s', $nick);
     *
     *   // Send it to the joinee privately
     *   $connection->sendMessage($nick, 'Welcome!');
     * </code>
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string channel which channel was joined
     * @param   string nick who joined
     */
    function onJoins(&$connection, $channel, $nick) { }

    /**
     * Callback for parts
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string channel which channel was part
     * @param   string nick who part
     * @param   string message the part message, if any
     */
    function onParts(&$connection, $channel, $nick, $message) { }
    
    /**
     * Callback for mode changes
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick who initiated the mode change
     * @param   string target what the mode setting is for (e.g. +k #channel, +i user)
     * @param   string mode the mode including a + or - as its first letter
     * @param   string params additional parameters
     */
    function onModeChanges(&$connection, $nick, $target, $mode, $params) { }
  
    /**
     * Callback for private messages
     *
     * Example (implementing "commands"):
     * <code>
     *   if (sscanf($message, "!%s %[^\r]", $command, $params)) {
     *     switch (strtolower($command)) {
     *       case 'status':
     *         // ...
     *     }
     *   }
     * </code>
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick
     * @param   string target
     * @param   string message
     */
    function onPrivateMessage(&$connection, $nick, $target, $message) { }

    /**
     * Callback for topic changes
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick who changed the topic
     * @param   string channel what channel the topic was changed for
     * @param   string topic the new topic
     */
    function onTopic(&$connection, $nick, $channel, $topic) { }

    /**
     * Callback for notices
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick
     * @param   string target
     * @param   string message
     */
    function onNotice(&$connection, $nick, $target, $message) { }

    /**
     * Callback for actions. Actions are when somebody writes /me ...
     * in their IRC window.
     *
     * Example (annoying:)):
     * <code>
     *   $connection->sendAction($target, 'imitates %s and %s, too', $nick, $params);
     * </code>
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick who initiated the action
     * @param   string target where action was initiated
     * @param   string action what actually happened (e.g. "looks around")
     */
    function onAction(&$connection, $nick, $target, $action) { }

    /**
     * Callback for CTCP VERSION
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick nick requesting version
     * @param   string target where version was requested
     * @param   string params additional parameters
     */
    function onVersion(&$connection, $nick, $target, $params) { }

    /**
     * Callback for CTCP USERINFO
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick nick requesting user information
     * @param   string target where user information was requested
     * @param   string params additional parameters
     */
    function onUserInfo(&$connection, $nick, $target, $params) { }

    /**
     * Callback for CTCP CLIENTINFO
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick nick requesting client information
     * @param   string target where client information was requested
     * @param   string params additional parameters
     */
    function onClientInfo(&$connection, $nick, $target, $params) { }

    /**
     * Callback for CTCP PING
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick nick requesting ping
     * @param   string target where ping was requested
     * @param   string params additional parameters
     */
    function onPing(&$connection, $nick, $target, $params) { }

    /**
     * Callback for CTCP TIME
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick nick requesting time
     * @param   string target where time was requested
     * @param   string params additional parameters
     */
    function onTime(&$connection, $nick, $target, $params) { }

    /**
     * Callback for CTCP FINGER
     *
     * @access  public
     * @param   &peer.irc.IRCConnection connection
     * @param   string nick nick requesting finger information
     * @param   string target where finger information was requested
     * @param   string params additional parameters
     */
    function onFinger(&$connection, $nick, $target, $params) { }
  
  }
?>
