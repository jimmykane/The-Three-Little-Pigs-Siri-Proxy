<?php

class PageManager {

    var $folderLocation;
    var $getAtt;
    var $pages;

    public function PageManager($folderLocation, $getAtt) {
        if (!empty($folderLocation)) {
            if (is_dir($folderLocation)) {
                $this->folderLocation = $folderLocation;
                $this->getAtt = $getAtt;
                $this->pages = simplexml_load_file($this->folderLocation . "/pages.xml");
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function navigation() {
        $return = '';

        $i = 0;
        $return .= '<ul>';
        foreach ($this->pages as $page) {


            if (!strcmp($page->name, 'Login')) {

                if (isUnloggedUser()) {
                    $return .= '<li';
                    if ($_GET[$this->getAtt] == $page->file || (empty($_GET[$this->getAtt]) && ++$i == 1)) {
                        $return .= ' id="active"';
                    }
                    $return .= '><a href="?' . $this->getAtt . '=' . $page->file . '">' . $page->name . '</a></li>';
                }
            } elseif (!strcmp($page->name, 'Logout')) {

                if (!isUnloggedUser()) {
                    $return .= '<li';
                    if ($_GET[$this->getAtt] == $page->file || (empty($_GET[$this->getAtt]) && ++$i == 1)) {
                        $return .= ' id="active"';
                    }
                    $return .= '><a href="?' . $this->getAtt . '=' . $page->file . '">' . $page->name . '</a></li>';
                }
            } else {
                $return .= '<li';
                if ($_GET[$this->getAtt] == $page->file || (empty($_GET[$this->getAtt]) && ++$i == 1)) {
                    $return .= ' id="active"';
                }
                $return .= '><a href="?' . $this->getAtt . '=' . $page->file . '">' . $page->name . '</a></li>';
            }
        }


        return $return;
    }

    public function getTitle() {
        $i = 0;
        foreach ($this->pages as $page) {
            if ($_GET[$this->getAtt] == $page->file || (empty($_GET[$this->getAtt]) && ++$i == 1)) {
                return $page->name;
            }
        }
    }

    public function getPageContent() {
        $i = 0;
        $foundPage = false;
        foreach ($this->pages as $page) {
            if ($_GET[$this->getAtt] == $page->file || (empty($_GET[$this->getAtt]) && ++$i == 1)) {
                $file = $this->folderLocation . "/" . $page->file . "." . $page->extension;
                if (file_exists($file)) {
                    include_once($file);
                    $foundPage = true;
                }
            }
        }

        if (!$foundPage) {
            include_once($this->folderLocation . "/404.html");
        }
    }

}

?>