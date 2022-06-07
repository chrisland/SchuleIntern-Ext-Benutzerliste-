<?php
/**
 *
 */
class extUserlistModelList
{

    /**
     * @var data []
     */
    private $data = [];

    private $members = false;
    private $owners = false;

    private $members_stats = [
        'count' => 0,
        'isEltern' => 0,
        'isPupil' => 0,
        'isTeacher' => 0,
        'isNone' => 0
    ];


    /**
     * Constructor
     * @param $data
     */
    public function __construct($data = false)
    {
        if (!$data) {
            $data = $this->data;
        }
        $this->setData($data);
    }

    /**
     * @return data
     */
    public function setData($data = [])
    {
        $this->data = $data;
        return $this->getData();
    }

    /**
     * @return data
     */
    public function getData() {
        return $this->data;
    }

    /**
     * @return
     */
    public function getStatsMember() {
        if ( !$this->members ) {
            $this->loadMembers();
        }
        return $this->members_stats;
    }

    /**
     * Getter
     */
    public function getID() {
        return $this->data['id'];
    }
    public function getCreatedTime() {
        return $this->data['createdTime'];
    }
    public function getCreatedBy() {
        return $this->data['createdBy'];
    }
    public function getTitle() {
        return $this->data['title'];
    }

    public function getMembers() {
        if ( !$this->members ) {
            $this->loadMembers();
        }
        return $this->members;
    }

    public function loadMembers() {

        include_once PATH_EXTENSIONS.'userlist'.DS.'models'.DS.'Member.class.php';
        $this->members = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_userlist_list_members WHERE list_id = ".$this->getID() );
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $user = user::getUserByID($data['user_id']);
            if (isset($this->members_stats[$user->getUserTyp(true)])) {
                $this->members_stats[$user->getUserTyp(true)]++;
                $this->members_stats['count']++;
            }
            //$this->members[] = $user;
            $this->members[] = new extUserlistModelMember($data, $user);
        }
    }

    public function getOwners() {
        if ( !$this->owners ) {
            $this->loadOwners();
        }
        return $this->owners;
    }

    public function loadOwners() {
        $this->owners = [];
        $dataSQL = DB::getDB()->query("SELECT * FROM ext_userlist_list_owner WHERE list_id = ".$this->getID() );
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $this->owners[] = user::getUserByID($data['user_id']);
        }
    }

    public function getCollection($full = false) {

        $collection = [
            "id" => $this->getID(),
            "title" => $this->getTitle()
        ];
        if ($full == true) {
            if ( $this->data['cck_id'] ) {
                include_once PATH_EXTENSIONS.'cck'.DS.'models'.DS.'Article.class.php';
                $collection["article"] = extCckModelArticle::getByID( $this->data['cck_id'] )->renderTemplate();
            }

        }

        return $collection;
    }



    /**
     * @return Array[]
     */
    public static function getAllByOwner($user_id = false) {

        if (!(int)$user_id) {
            return false;
        }
        $ret =  [];
        $dataSQL = DB::getDB()->query("SELECT  b.*
            FROM ext_userlist_list_owner as a
            LEFT JOIN ext_userlist_list as b ON a.list_id = b.id
            WHERE a.user_id =  ".(int)$user_id);
        while ($data = DB::getDB()->fetch_array($dataSQL, true)) {
            $ret[] = new self($data);
        }
        return $ret;
    }

    /**
     * @return Array[]
     */
    public static function getByID($id = false, $user_id = false) {

        if (!(int)$id) {
            return false;
        }
        if (!(int)$user_id) {
            return false;
        }
        $dataSQL = DB::getDB()->query_first("SELECT  b.*
            FROM ext_userlist_list_owner as a
            LEFT JOIN ext_userlist_list as b ON a.list_id = b.id
            WHERE a.list_id =  ".(int)$id." AND a.user_id =  ".(int)$user_id, true);

        if ($dataSQL) {
            return new self($dataSQL);
        }

    }





}