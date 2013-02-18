<?php
define("START_ID", 1);
define("STOP_TED_QUERY",20);
/**
 * this script will run as a crone job and will go over all pages
 * on TED http://www.ted.com/talks/view/id/ 
 * from id 1 till there are no more pages
 * after this each page will be parsed and take the title and tags from the page
 * last step is to query youtube API to get the url of the movie and more details
 */

//example to get movies from youtube API
//https://gdata.youtube.com/feeds/api/videos?author=TEDtalksDirector&q=Questioning%20the%20universe%20&v=2&alt=json

/**
 * function get a file using curl (fast)
 * @param $url - url which we want to get its content
 * @return the data of the file
 * @author XXXXX
 */
function file_get_contents_curl($url)
{
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);

	$data = curl_exec($ch);
	curl_close($ch);

	return $data;
}

/**
 * will represent a TED talk with all its details from the ted web site and from youTube
 * @author XXXXX
 *
 */
class Talk{

	var $id;
	var $name;
	var $tags = array();
	var $youTubeID;
	var $label;
	var $src;
	var $viewCount;
	var $youTubeTitle;


	function setViewCount($count){
		$this->viewCount=$count;
	}
	
	function getViewCount(){
		return $this->viewCount;
	}
	
	function setYouTubeTitle($title){
		$this->youTubeTitle=$title;
	}
	
	function getYouTubeTitle(){
		return $this->youTubeTitle;
	}
	
	function setYouTubeID($ID){
		$this->youTubeID=$ID;
	}
	
	function getYouTubeID(){
		return $this->youTubeID;
	}
	
	function setLabel($Label){
		$this->label=$Label;
	}
	
	function getLabel(){
		return $this->label;
		
	}
	
	function setSrc($SRC){
		$this->src=$SRC;
	}
	
	function getSrc(){
		return $this->src;
	}
	function setID($ID){
		$this->id=$ID;
	}

	function getID(){
		return $this->id;
	}
	function setName($Name){
		$this->name=$Name;
	}

	function getName(){
		return $this->name;
	}

	function setTags($Tags){
		$this->tags=$Tags;
	}
	function addTag($tag){
		$this->tags[]=$tag;
	}

	function getTags(){
		return $this->tags;
	}
}

//will hold all talks in array
$tedTalks = array();

//id to start the query from
$id=START_ID;

//will indicate when needed to stop the query beacuse reached the end id's on TED website
$endOFQuery=0;

//get the time
$time_start = microtime(true);

//start the query on TED website
//if we will query 20 pages in a row that do not exsist we will stop the querys and assume there are no more
while ($endOFQuery < STOP_TED_QUERY){

	//get the page of the talk
	$html = file_get_contents_curl("http://www.ted.com/talks/view/id/$id");

	//parsing begins here:
	$doc = new DOMDocument();
	@$doc->loadHTML($html);
	$nodes = $doc->getElementsByTagName('title');

	//get and display what you need:
	$title = $nodes->item(0)->nodeValue;



	//check if this a valid page
	if (! strcmp ($title ,"TED | Talks"))
		//this is a removed ted talk or the end of the query so raise a flag (if we get anough of these in a row we will stop)
		$endOFQuery++;
	else {
		//this is a valid TED talk get its details

		//reset the flag for end of query
		$endOFQuery = 0;

		//get meta tags
		$metas = $doc->getElementsByTagName('meta');

		//get the tag we need (keywords)
		for ($i = 0; $i < $metas->length; $i++)
		{
			$meta = $metas->item($i);
			if($meta->getAttribute('name') == 'keywords')
				$keywords = $meta->getAttribute('content');
		}

		//create new talk object and populate it
		$talk = new Talk();
		//set its ted id from ted web site
		$talk->setID($id);
		//parse the name (name has un-needed char's in the end)
		$talk->setName( substr($title, 0, strpos( $title, '|')) );

		//parse the String of tags to array
		$keywords = explode(",", $keywords);
		//remove un-needed items from it
		$keywords=array_diff($keywords, array("TED","Talks"));

		//add the filters tags to the talk
		$talk->setTags($keywords);

		//add to the total talks array
		$tedTalks[]=$talk;
	}

	//move to the next ted talk ID to query
	$id++;
} //end of the while

//going over all ted talk collected from ted web site and getting youTube details
foreach ($tedTalks as $talk){
	
	//prepare the query string with the TED TALK name as the search string
	$queryString = "https://gdata.youtube.com/feeds/api/videos?author=TEDtalksDirector&q=".$talk->getName()."&v=2&alt=json";
	//remove all spaces to URL friendly string
	$queryString = str_replace(" ","%20",$queryString);
	//get details from API in json format
	$resultString = file_get_contents($queryString);
		
	//parse result string into workable json file
	$responseJson=json_decode($resultString,true);
	//get the first movie details
	$movieDetails = $responseJson['feed']['entry'][0];
	//get all details (this section is for specific data i need you can skip this or change this to something else)
	$talk->setYouTubeID(substr($movieDetails['id']['$t'],strpos( $movieDetails['id']['$t'], 'video:')+6));
	$talk->setLabel($movieDetails['category'][1]['label']);
	$talk->setYouTubeTitle($movieDetails['title']['$t']);
	$talk->setSrc($movieDetails['link'][0]['href']);
	$talk->setViewCount($movieDetails['yt$statistics']['viewCount']);
	
	//insert into DB if this movie has details from youtube and from TED site
	if ($youTubeID!=null){
		//insert the talk into DB
	}
	
}

$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
echo "this took (sec) : ".$execution_time;

?>
