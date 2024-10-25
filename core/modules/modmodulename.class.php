<?php
include_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

class modmodulename extends DolibarrModules {
    function __construct($db) {
        global $langs;
        $this->db = $db;
        $this->numero = 104000; 
        $this->rights_class = 'modulename';
        $this->family = "financial";
        $this->name = preg_replace('/^mod/i', '', get_class($this));
        $this->description = "Module for cheque rejection with reason.";
        $this->version = '1.0';
        $this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        $this->picto='modulename@modulename';
        $this->module_parts = array();
        $this->dirs = array('/modulename');
        $this->config_page_url = array("modulename_setup.php@modulename");
        $this->langfiles = array("modulename@modulename");
        $this->depends = array(); 
        $this->requiredby = array();
        $this->conflictwith = array(); // Corrected this line
        $this->phpmin = array(5, 3);
        $this->need_dolibarr_version = array(3, 0);

        // Constants
        $this->const = array();

        // Define rights
        $this->rights = array();
        $r = 0;
        $this->rights[$r][0] = 104001;
        $this->rights[$r][1] = 'Read modulename';
        $this->rights[$r][2] = 'r';
        $this->rights[$r][3] = 0;
        $this->rights[$r][4] = 'read';
        $r++;

        // Define menus
        $this->menu = array();
        $r = 0;
        // Main menu entry
        $this->menu[$r] = array(
            'fk_menu' => 'fk_mainmenu=compta',
            'type' => 'top',
            'titre' => 'My Module',
            'mainmenu' => 'modulename',
            'leftmenu' => 'modulename_top',
            'url' => '/custom/modulename/page.php',
            'langs' => 'modulename@modulename',
            'position' => 50010,
            'enabled' => '$conf->modulename->enabled',
            'perms' => '1',
            'target' => '',
            'user' => 2
        );
        $r++;
        // Submenu entry
        $this->menu[$r] = array(
            'fk_menu' => 'fk_mainmenu=modulename',
            'type' => 'left',
            'titre' => 'Cheque Rejection',
            'mainmenu' => 'modulename',
            'leftmenu' => 'modulename_rejection',
            'url' => '/custom/modulename/subpage.php',
            'langs' => 'modulename@modulename',
            'position' => 50011,
            'enabled' => '$conf->modulename->enabled',
            'perms' => '1',
            'target' => '',
            'user' => 2
        );
    }

    function init($options = '') {
        $sql = array();

        // Add reason_rejet_cheque column if not exists
        $result = $this->db->query("SHOW COLUMNS FROM llx_paiement LIKE 'reason_rejet_cheque'");
        if ($this->db->num_rows($result) == 0) {
            $sql[] = "ALTER TABLE llx_paiement ADD reason_rejet_cheque TEXT;";
        }

        // Hide original cheque folder
        $originalChequeFolder = DOL_DOCUMENT_ROOT . '/compta/paiement/cheque';
        $backupChequeFolder = DOL_DOCUMENT_ROOT . '/compta/paiement/cheque_backup';
        if (!file_exists($backupChequeFolder)) {
            rename($originalChequeFolder, $backupChequeFolder);
        }

        // Link custom cheque folder
        symlink(DOL_DOCUMENT_ROOT . '/custom/modulename/cheque', $originalChequeFolder);

        return $this->_init($sql, $options);
    }

    function remove($options = '') {
        $sql = array();
        $sql[] = "ALTER TABLE llx_paiement DROP COLUMN reason_rejet_cheque;";

        // Remove the custom cheque folder link
        $originalChequeFolder = DOL_DOCUMENT_ROOT . '/compta/paiement/cheque';
        $backupChequeFolder = DOL_DOCUMENT_ROOT . '/compta/paiement/cheque_backup';
        if (is_link($originalChequeFolder)) {
            unlink($originalChequeFolder);
        }

        // Restore the original cheque folder
        if (file_exists($backupChequeFolder)) {
            rename($backupChequeFolder, $originalChequeFolder);
        }

        return $this->_remove($sql, $options);
    }
}
