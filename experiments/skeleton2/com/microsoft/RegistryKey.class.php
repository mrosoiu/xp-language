<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */

  uses(
    'com.microsoft.RegistryException',
    'com.microsoft.wscript.WshShell'
  );

  /**
   * Registry
   *
   * Usage example (reading a key)
   * <code> 
   *   uses('com.microsoft.RegistryKey');
   *   
   *   $k= new RegistryKey($argv[1]);
   *   printf('Reading key %s (exists: %d)', $argv[1], $k->exists());
   *   
   *   try(); {
   *     $value= $k->getValue();
   *   } if (catch('RegistryException', $e)) {
   *     $e->printStackTrace();
   *     exit(-1);
   *   }
   *   
   *   var_dump($value);
   * </code>
   *
   * Usage example (creating a key)
   * <code> 
   *   uses('com.microsoft.RegistryKey');
   *   
   *   $k= new RegistryKey($argv[1]);
   *   printf('Creating key %s and setting its value to 6100 (REG_DWORD)', $argv[1]);
   *   
   *   try(); {
   *     $k->setValue(6100, REG_DWORD);
   *     $value= $k->getValue();
   *   } if (catch('RegistryException', $e)) {
   *     $e->printStackTrace();
   *     exit(-1);
   *   }
   *   
   *   var_dump($value);
   * </code>
   *
   * @see      http://msdn.microsoft.com/library/en-us/script56/html/wsmthregwrite.asp?frame=true
   * @see      http://msdn.microsoft.com/library/default.asp?url=/library/en-us/script56/html/wsObjWScript.asp
   * @ext      com
   * @purpose  Registry access
   * @platform Windows
   */
  class RegistryKey extends Object {
    const
      REG_SZ = 'REG_SZ',
      REG_EXPAND_SZ = 'REG_EXPAND_SZ',
      REG_DWORD = 'REG_DWORD',
      REG_BINARY = 'REG_BINARY';

    public 
      $name = '';
       
    public
      $_sh  = NULL;
  
    /**
     * Constructor
     *
     * @access  public
     * @param   string name e.g. HKEY_CURRENT_USER\Environment\TMP
     */    
    public function __construct($name) {
      
      $this->name= $name;
      $this->_sh= WshShell::getInstance();
    }
    
    /**
     * Get this key's name
     *
     * @access  public
     * @return  string
     */
    public function getName() {
      return $this->name;
    }
    
    /**
     * Checks whether this key exists
     *
     * @access  public
     * @return  bool
     */
    public function exists() {
    
      // Really ugly, but the WshShell object does not have a 
      // regExists() method
      return (NULL !== @$this->_sh->regRead($this->name));
    }
    
    /**
     * Deletes this key
     *
     * @access  public
     * @return  bool
     * @throws  com.microsoft.RegistryException
     */
    public function delete() {
      if (NULL === $this->_sh->regDelete($this->name)) {
        throw (new RegistryException('Could not delete key "'.$this->name.'"'));        
      }
      return TRUE;
    }
    
    /**
     * Read this key's value
     *
     * @access  public
     * @return  &mixed   
     * @throws  com.microsoft.RegistryException
     */
    public function getValue() {
      if (NULL === ($v= $this->_sh->regRead($this->name))) {
        throw (new RegistryException('Could not read key "'.$this->name.'"'));  
      }
      return $v;
    }
    
    /**
     * Set this key's value. Creates the key if necessary.
     *
     * @access  public
     * @param   mixed val
     * @param   string type default REG_SZ
     * @return  bool
     * @throws  com.microsoft.RegistryException
     */
    public function setValue($val, $type= REG_SZ) {
      if (NULL === $this->_sh->regWrite($this->name, $val, $type)) {
        throw (new RegistryException('Could not write key "'.$this->name.'"'));        
      }
      return TRUE;
    }
  }
?>
