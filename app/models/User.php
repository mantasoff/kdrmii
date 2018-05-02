<?php
/**
 * Created by d0Nt
 * Date: 2018.03.24
 * Time: 23:20
 */

namespace app\models;


use core\Database\Field;
use core\Database\Mysql;
use core\Database\Query;
use core\Model;

class User extends Model
{
    protected static $table = "users";
    protected static $selectFields = ["id", "email", "password", "institution", "degree", "first_name",
        "last_name", "affiliation", "phone_number", "article_title", "article_authors", "hotel", "leading_people",
        "abstract", "additional_events"];
    protected static $saveFields = ["email", "password", "institution", "degree", "first_name", "last_name", "affiliation",
        "phone_number", "article_title", "article_authors", "hotel", "leading_people", "abstract", "additional_events"];
    /**
     * @var string Static salt for hashing passwords
     */
    private $salt = "D1ISxMojS4g1FRmSAGsd";

    /**
     * Set hashed password for user
     * @param $unHashed string password to set
     */
    public function setPassword($unHashed){
        $this->password = hash('sha256', $this->salt."".$unHashed);
    }

    /**
     * Validate user data before creating
     * @param $data
     * @return bool|string
     */
    public static function validateData($data){
        if(!is_array($data) || count($data) === 0){
            return "No data given";
        }
        $requiredParams=["title", "firstname", "lastname", "institution", "affiliation", "email", "phone", "articletitle",
            "articleauthors", "articleauthorsaffiliations", "hotel", "leading_people", "invoice_required", "abstract"];
        foreach ($requiredParams as $param){
            if(!isset($data[$param]) || $data[$param] === null || $data[$param] === "" || strlen($data[$param])<2){
                return $param." is required";
            }
        }
        if(preg_match('/^[A-Za-z0-9.]+@[A-Za-z]+.[A-Za-z0-9.]+$/', $data["email"]) === 0 || preg_match('/^[A-Za-z0-9.]+@[A-Za-z]+.[A-Za-z0-9.]+$/', $data["email"]) === false){
            return "Bad mail value";
        }
        if(!in_array($data["hotel"], ["roomother", "roomno", "roomsingle", "roomdouble"])){
            return "Bad hotel value";
        }
        if($data["hotel"] === "roomother" && (!isset($data["otherroom"]) || $data["otherroom"] === null || $data["otherroom"] === "" || strlen($data["otherroom"])<2)) {
            return "Additional information about room is required";
        }
        if(!in_array($data["leading_people"], ["accyes", "accno"])){
            return "Bad accompany value";
        }
        if(!in_array($data["additional_events"], ["accevyes","accevno"])){
            return "Bad additional events value";
        }
        if(!in_array($data["invoice_required"], ["invyes","invno"])){
            return "Bad invoice value";
        }
        if($data["invoice_required"] === "invyes"){
            foreach (["institutionname","institutionaddress", "institutioncompanycode", "institutionbankcode"] as $param){
                if(!isset($data[$param]) || $data[$param] === null || $data[$param] === "" || strlen($data[$param])<2){
                    return $param." is required";
                }
            }
        }
        if(strlen($data["email"]) > 100){
            return "Mail is too long.";
        }
        if(strlen($data["institution"]) > 100){
            return "Institution is too long.";
        }
        if(strlen($data["degree"])>12){
            return "Degree is too long";
        }
        if(strlen($data["firstname"]) > 64){
            return "First name is too long.";
        }
        if(strlen($data["lastname"]) > 64){
            return "Last name is too long.";
        }
        if(strlen($data["affiliation"]) > 255){
            return "Affiliation is too long.";
        }
        if(strlen($data["phone"]) > 18){
            return "Phone number is too long.";
        }
        if(strlen($data["articletitle"]) > 255){
            return "Article title is too long.";
        }
        if(strlen($data["articleauthors"]) > 300){
            return "Article authors is too long.";
        }
        if(strlen($data["articleauthorsaffiliations"]) > 300){
            return "Article authors affiliations is too long.";
        }
        if(strlen($data["abstract"]) > 300){
            return "Abstraction is too long.";
        }
        if(strlen($data["hotel"]) > 64){
            return "Hotel name is too long.";
        }


        $withMail = User::getByFields([new Field("email", $data["email"])]);
        if($withMail !== null){
            return "User with this email already exist.";
        }
        return true;
    }

    /**
     * Create user in database
     * @param $data
     * @return int
     */
    public static function create($data)
    {
        $user = new User();
        $user->email = $data["email"];
        $user->first_name = $data["firstname"];
        $user->last_name = $data["lastname"];
        $user->degree = $data["title"];
        $user->institution = $data["institution"];
        $user->affiliation = $data["affiliation"];
        $user->phone_number = $data["phone"];
        $user->article_title = $data["articletitle"];
        $user->article_authors = $data["title"];
        if(in_array($data["hotel"], ["roomno","roomsingle", "roomdouble"]))
            $user->hotel = $data["hotel"];
        else
            $user->hotel = $data["otherroom"];
        $user->leading_people = ($data["leading_people"] === "accyes" ? true : false );
        if($user-> leading_people)
            $user->additional_events = ($data["additional_events"] === "accevyes" ? true : false );
        $user->abstract = $data["abstract"];
        $id=$user->insert();
        Validation::createUserValidation($id);
        return 1;
    }
}