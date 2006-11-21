<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses('util.Date');

  /**
   * Represent an FTP entry
   *
   * @see      xp://peer.ftp.FtpDir#getEntry
   * @test     xp://net.xp_framework.unittest.peer.FtpRawListTest
   * @purpose  Base class
   */
  class FtpEntry extends Object {
    var
      $name         = '',
      $permissions  = 0,
      $numlinks     = 0,
      $user         = '',
      $group        = '',
      $size         = 0,
      $date         = NULL,
      $connection   = NULL;
      
    /**
     * Constructor
     *
     * @access  public
     * @param   string name
     */
    function __construct($name) {
      $this->name= $name;
    }

    /**
     * Set Permissions. Takes either a string or an integer as argument.
     * In case a string is passed, it should have the following form:
     *
     * <pre>
     *   rwxr-xr-x  # 755
     *   rw-r--r--  # 644
     * </pre>
     *
     * @access  public
     * @param   mixed perm
     * @throws  lang.IllegalArgumentException
     */
    function setPermissions($perm) {
      static $m= array('r' => 4, 'w' => 2, 'x' => 1, '-' => 0);

      if (is_string($perm) && 9 == strlen($perm)) {
        $this->permissions= (
          ($m[$perm{0}] | $m[$perm{1}] | $m[$perm{2}]) * 100 +
          ($m[$perm{3}] | $m[$perm{4}] | $m[$perm{5}]) * 10 +
          ($m[$perm{6}] | $m[$perm{7}] | $m[$perm{8}])
        );
      } else if (is_int($perm)) {
        $this->permissions= $perm;
      } else {
        return throw(new IllegalArgumentException('Expect: string(9) / int, have "'.$perm.'"'));
      }
    }

    /**
     * Get Permissions
     *
     * @access  public
     * @return  int
     */
    function getPermissions() {
      return $this->permissions;
    }

    /**
     * Set Numlinks
     *
     * @access  public
     * @param   int numlinks
     */
    function setNumlinks($numlinks) {
      $this->numlinks= $numlinks;
    }

    /**
     * Get Numlinks
     *
     * @access  public
     * @return  int
     */
    function getNumlinks() {
      return $this->numlinks;
    }

    /**
     * Set User
     *
     * @access  public
     * @param   string user
     */
    function setUser($user) {
      $this->user= $user;
    }

    /**
     * Get User
     *
     * @access  public
     * @return  string
     */
    function getUser() {
      return $this->user;
    }

    /**
     * Set Group
     *
     * @access  public
     * @param   string group
     */
    function setGroup($group) {
      $this->group= $group;
    }

    /**
     * Get Group
     *
     * @access  public
     * @return  string
     */
    function getGroup() {
      return $this->group;
    }

    /**
     * Set Size
     *
     * @access  public
     * @param   int size
     */
    function setSize($size) {
      $this->size= $size;
    }

    /**
     * Get Size
     *
     * @access  public
     * @return  int
     */
    function getSize() {
      return $this->size;
    }

    /**
     * Set Date
     *
     * @access  public
     * @param   &util.Date date
     */
    function setDate(&$date) {
      $this->date= &$date;
    }

    /**
     * Get Date
     *
     * @access  public
     * @return  &util.Date
     */
    function &getDate() {
      return $this->date;
    }

    /**
     * Set Name
     *
     * @access  public
     * @param   mixed name
     */
    function setName($name) {
      $this->name= $name;
    }

    /**
     * Get Name
     *
     * @access  public
     * @return  string
     */
    function getName() {
      return $this->name;
    }
    
    /**
     * Creates a string representation of this object
     *
     * @access  public
     * @return  string
     */
    function toString() {
      return sprintf(
        "%s(name= %s) {\n".
        "  [permissions ] %d\n".
        "  [numlinks    ] %d\n".
        "  [user        ] %s\n".
        "  [group       ] %s\n".
        "  [size        ] %d\n".
        "  [date        ] %s\n".
        "}",
        $this->getClassName(),
        $this->name,
        $this->permissions,
        $this->numlinks,
        $this->user,
        $this->group,
        $this->size,
        xp::stringOf($this->date)
      );
    }
  }
?>
