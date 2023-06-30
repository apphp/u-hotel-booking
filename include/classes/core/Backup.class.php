<?php

/***
 *	Class Backup
 *  -------------- 
 *  Description : encapsulates backup operations & properties
 *	Written by  : ApPHP
 *	Version     : 1.0.3
 *  Updated	    : 13.09.2016
 *	Usage       : Core Class (ALL)
 *	Differences : no
 *	
 *	PUBLIC:				  	STATIC:				 	PRIVATE:
 * 	------------------	  	---------------     	---------------
 * 	__construct										ExecuteSqlDump
 * 	__destruct
 * 	ShowPreviousBackups
 * 	DeleteBackup
 * 	RestoreBackup
 * 	ExecuteBackup
 * 	DrawInstallationForm
 * 	DrawRestoreForm
 *
 *  1.0.3
 *      - changed "\n"
 *      - added changes for RTL languages
 *      - added nowrap
 *      -
 *      -
 *  1.0.2
 *      - [ <-> ] for links
 *      - fixed error on non-writable file
 *      - changed error message on wrong access permissions
 *      - added DrawInstallationForm and DrawRestoreForm
 *      - changed " with '
 *	
 **/

class Backup {
	
	protected $backupDirectory;
	protected $backupFilePrefix;
	protected $backupFileExt;
	
	public $error;
	
	//==========================================================================
    // Class Constructor
	//==========================================================================
	function __construct()
	{
		$this->backupDirectory = 'tmp/backup/';
		$this->backupFilePrefix = 'db-backup-';
		$this->backupFileExt = '.sql'; 
	}
	
	//==========================================================================
    // Class Destructor
	//==========================================================================
    function __destruct()
	{
		// echo 'this object has been destroyed';
    }

	/***
	 * 	Show list of previous backups
	 * @param $link_type - type of link
	 * @param $draw
	 */
	public function ShowPreviousBackups($link_type = 'delete', $draw = true)
	{
		$output = '';
		
		function DateCompare($a, $b){
			return ($a[1] <= $b[1]) ? 0 : 1;
		}
	
		$files = array();
		if(!file_exists($this->backupDirectory)) { @mkdir($this->backupDirectory,0755); }
		if($handle = @opendir($this->backupDirectory)){
			// loop over the directory
			while(false !== ($file = readdir($handle))){
				if(preg_match('/'.$this->backupFilePrefix.'/', $file) && preg_match('/.sql/', $file)){
					$files[] = array($file, time() - filemtime($this->backupDirectory.$file), filesize($this->backupDirectory.$file));
				}
			}		
			closedir($handle);
		}		
		
		// sort files by date
		usort($files, 'DateCompare');

		foreach($files as $key => $val){
			$fname = str_replace(array($this->backupFilePrefix, $this->backupFileExt), '', $val[0]);
			$output .= '<tr><td>&nbsp;</td>
							<td nowrap> &nbsp;'.$val[0].'&nbsp; </td>
							<td nowrap align="'.Application::Get('defined_right').'">'.number_format(($val[2]/1024), 0, '.', ',').' KB</td>';
			if($link_type == 'delete'){
				$output .= '<td>&nbsp;</td><td nowrap><a href="'.APPHP_BASE.'index.php?admin=mod_backup_installation&st=delete&fname='.$fname.'" onclick="return confirm(\''._BACKUP_DELETE_ALERT.'\')">[ '._DELETE_WORD.' ]</a></td><td>&nbsp;</td>';
			}elseif($link_type == 'restore'){
				$output .= '<td>&nbsp;</td><td nowrap><a href="'.APPHP_BASE.'index.php?admin=mod_backup_restore&st=restore&fname='.$fname.'" onclick="return confirm(\''._BACKUP_RESTORE_ALERT.'?\')">[ '._RESTORE.' ]</a></td><td>&nbsp;</td>';
			}
			$output .= '</tr>';
		}
		
		if(count($files) == 0){
			$output .= '<tr><td colspan="2">&nbsp;'._BACKUP_EMPTY_MSG.'&nbsp;</td></tr>';
		}
		
		if($draw) echo $output;
		else return $output;
	}

	/***
	 * Delete backup file
	 * @param $backup_file - backup file name
	 */
	public function DeleteBackup($backup_file = '')
	{
        // block all operations on demo version
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;				
		}
        
		$backup_file = $this->backupDirectory.$this->backupFilePrefix.$backup_file.$this->backupFileExt;
		
		if($backup_file == ''){
			$this->error = _BACKUP_EMPTY_NAME_ALERT;
			return false;
		}
		
        if(@unlink($backup_file)){
            return true;		
        }else{
            $this->error = _FILE_DELETING_ERROR;
            return false;
        }
	}

	/**
	 * Restore backup the db OR just a table
	 * @param $backup_file - backup file name
	 */
	public function RestoreBackup($backup_file = '')
	{
        // block all operations on demo version
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;				
		}

		$backup_file = $this->backupDirectory.$this->backupFilePrefix.$backup_file.$this->backupFileExt;

		if($backup_file == ''){
			$this->error = _BACKUP_EMPTY_NAME_ALERT;
			return false;
		}
		
        $sql_dump = file_get_contents($backup_file);
        if ($this->ExecuteSqlDump($sql_dump)){
            return true;
        }else{
            $this->error = _BACKUP_RESTORING_ERROR;
            return false;
        }
	}

	/**
	 * Execute backup the db OR just a table
	 * @param $backup_file - backup file name
	 * @param $tables - teables to backup
	 */
	public function ExecuteBackup($backup_file = '', $tables = '*')
	{
		$return = '';
		$nl = "\n";
		
        // block all operations on demo version
		if(strtolower(SITE_MODE) == 'demo'){
			$this->error = _OPERATION_BLOCKED;
			return false;				
		}

		if($backup_file == ''){
			$this->error = _BACKUP_EMPTY_NAME_ALERT;
			return false;
		}
		
        // save all tables
        if($tables == '*'){
            $tables = array();			
            $result = database_query('SHOW TABLES', DATA_ONLY, ALL_ROWS, FETCH_ARRAY);
            foreach($result as $key){
				if(preg_match('/'.DB_PREFIX.'/i', $key[0])) $tables[] = $key[0];
            }
        }else{
            $tables = is_array($tables) ? $tables : explode(',',$tables);
        }
        
        // run cycle through
        foreach($tables as $table){
            $num_fields = database_query('SELECT * FROM '.$table, FIELDS_ONLY);
            $result = database_query('SELECT * FROM '.$table, DATA_ONLY, ALL_ROWS, FETCH_ARRAY);

            $return .= 'DROP TABLE IF EXISTS '.$table.';';
            $row2 = database_query('SHOW CREATE TABLE '.$table, DATA_ONLY, FIRST_ROW_ONLY, FETCH_ARRAY);
            $return .= $nl.$nl.$row2[1].';'.$nl.$nl;
            
            foreach($result as $row){
                $return .= 'INSERT INTO '.$table.' VALUES(';
                for($j=0; $j<$num_fields; $j++){
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = preg_replace('/\n/','\\n',$row[$j]);
                    if(isset($row[$j])) { $return .= '"'.$row[$j].'"' ; } else { $return .= '""'; }
                    if($j<($num_fields-1)) $return .= ','; 
                }
                $return .= ');'.$nl;
            }
            $return .= $nl.$nl.$nl;
        }
        
        $backup_file_name = ($backup_file == '') ? date('M-d-Y') : $backup_file;
        $backup_file_path = $this->backupDirectory.$this->backupFilePrefix.$backup_file_name.$this->backupFileExt;
        
        //save file
        @chmod($backup_file_path, 0755);
        $handle = @fopen($backup_file_path,'w+');
        if($handle){
            @fwrite($handle,$return);
            @fclose($handle);
            $result = true;
        }else{
            $this->error = _BACKUP_EXECUTING_ERROR;
            $result = false;
        }
        @chmod($backup_file_path, 0644);
        return $result;
	}
	
	/**
	 * Execute SqlDump
	 * @param $restore_query - resore query or list of queries
	 */
	private function ExecuteSqlDump($restore_query)
	{
		$nl = "\n";
		$sql_array = array();
		$sql_length = strlen($restore_query);
		$pos = strpos($restore_query, ';');
		for($i=$pos; $i<$sql_length; $i++){
			if($restore_query[0] == '#'){
				$restore_query = ltrim(substr($restore_query, strpos($restore_query, $nl)));
				$sql_length = strlen($restore_query);
				$i = strpos($restore_query, ';')-1;
				continue;
			}
			if($restore_query[($i+1)] == $nl){
				for($j=($i+2); $j<$sql_length; $j++){
					if(trim($restore_query[$j]) != ''){
						$next = substr($restore_query, $j, 6);
						if($next[0] == '#'){
							// remove line  where the break position (#comment line)
							for($k=$j; $k<$sql_length; $k++){
								if($restore_query[$k] == $nl) break;
							}
							$query = substr($restore_query, 0, $i+1);
							$restore_query = substr($restore_query, $k);
							// join 2 parts of query
							$restore_query = $query.$restore_query;
							$sql_length = strlen($restore_query);
							$i = strpos($restore_query, ';')-1;
							continue 2;
						}
						break;
					}
				}
				if($next == ''){ // get last insert query
					$next = 'insert';
				}
				if((preg_match('/create/i', $next)) || (preg_match('/insert/i', $next)) || (preg_match('/drop t/i', $next))){
					$next = '';
					$sql_array[] = substr($restore_query, 0, $i);
					$restore_query = ltrim(substr($restore_query, $i+1));
					$sql_length = strlen($restore_query);
					$i = strpos($restore_query, ';')-1;
				}
			}
		}
	
		for($i=0; $i<sizeof($sql_array); $i++){
			if(!@database_void_query($sql_array[$i])){
				return false;
			}
		}
		
		return true;	
	}
	
	/**
	 * 	Draw Installation form
	 */
	public function DrawInstallationForm()
	{
		$output = '<div class="table-responsive">
		<table align="center" width="100%" border="0" cellspacing="0" cellpadding="3" class="main_text">
		<tr valign="top">
			<td width="45%">
				<form action="'.APPHP_BASE.'index.php?admin=mod_backup_installation" method="post">
				'.draw_hidden_field('submition_type', '1', false).'
				'.draw_token_field(false).'
				<table align="center" width="100%" border="0" cellspacing="0" cellpadding="3" class="main_text">
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr>
					<td align="'.Application::Get('defined_left').'" colspan="2"><b>'._BACKUP_YOUR_INSTALLATION.': </b></td>		
				</tr>
				<tr>
					<td align="'.Application::Get('defined_left').'" width="1%"><input type="text" name="backup_file" value="'.date('M-d-Y').'" size="24" maxlength="20" /></td>
					<td align="'.Application::Get('defined_left').'"><input class="form_button" type="submit" name="submit" value="'._BACKUP.'" /></td>
				</tr>
				</table>
				</form>
			</td>
			<td width="55%">
				<table align="center" width="100%" border="0" cellspacing="0" cellpadding="3" class="main_text">
				<tr><td colspan="2">&nbsp;</td></tr>
				<tr>
					<td align="'.Application::Get('defined_left').'" colspan="2"><b>'._BACKUPS_EXISTING.': </b></td>		
				</tr>
				'.$this->ShowPreviousBackups('delete', false).'
				</table>			
			</td>
		</tr>
		</table>
		</div>';
		
		echo $output;
	}	
	
	/**
	 * 	Draw Restore form
	 */
	public function DrawRestoreForm()
	{
		$output = '<table align="center" width="96%" border="0" cellspacing="1" cellpadding="3" class="main_text">
		<tr><td colspan="6">&nbsp;</td></tr>
		<tr>
			<td align="'.Application::Get('defined_left').'" colspan="5" nowrap>&nbsp;<b>'._BACKUP_CHOOSE_MSG.':</b>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr><td colspan="6" height="5px" nowrap></td></tr>
		'.$this->ShowPreviousBackups('restore', false).'
		</table>';
		
		echo $output;
	}	

}
