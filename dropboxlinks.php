<?php
/*----------------------------------------------------------------------------------|  www.vdm.io  |----/
				Vast Development Method 
/-------------------------------------------------------------------------------------------------------/

	@version		1.0.0 - 01st July, 2015
	@package		Dropbox Links builder
	@subpackage		dropboxlinks.php
	@author			Llewellyn van der Merwe <http://www.vdm.io>	
	@copyright		Copyright (C) 2015. All Rights Reserved
	@license		GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html
	
/------------------------------------------------------------------------------------------------------*/


/**
 * DropboxLinks class
 */
class DropboxLinks
{
	protected $subFolders;
	
	/**
	 * Constructor
	 */
	public function __construct($mainurl)
	{
		// set all sub folders
		$this->subFolders = $this->getLinks($mainurl,'?dl=0');
		
		// start building the json files
		$this->start();
	}
	
	protected function setJson($data)
	{
		if ($this->checkObject($data))
		{
			$data = json_encode($data);
			return $data;
		}
		return '';
	}
	
	protected function start()
	{
		if ($this->checkArray($this->subFolders))
		{
			foreach ($this->subFolders as $subFolder)
			{
				// get the folder name
				$folder = str_replace('?dl=0','',substr($subFolder, strrpos($subFolder, '/') + 1));
				if ($this->checkString($folder))
				{
					// now get the audio links in each sub folder
					$audioLinks = $this->getLinks($subFolder,'.mp3?dl=0');
					// set the data of the audio files
					$data = $this->setJson($this->getLinkData($audioLinks));
					// now save this folders dat to a file since it is to bog to do all in one file
					$this->saveJson($data,$folder.'.json');
					// make sure we have no data conflict
					unset($audioLinks);
					unset($data);
				}
			}			
		}
	}
	
	protected function saveJson($data,$filename)
	{
		if ($this->checkString($data))
		{
			/*			
				for now it will save
				the files in the same
				directory as the php
				file where the class
				is called we will have
				to change this to suite
				your custom needs.
			*/
			$fp = fopen($filename, 'w');
			fwrite($fp, $data);
			fclose($fp);
		}
	}
	
	protected function getLinkData($urls)
	{
		if ($this->checkArray($urls))
		{
			$buket = new stdClass();
			foreach ($urls as $url)
			{
				$name = str_replace('.mp3?dl=0','',substr($url, strrpos($url, '/') + 1));
				$buket->{$name} = array('link' => str_replace('?dl=0','?dl=1',$url), 'name' => $name.'.mp3');
			}
			return $buket;
		}
		return false;
	}
	
	protected function getLinks($url,$search,$not = '.html')
	{
		if ($this->checkString($url))
		{
			$html = file_get_contents($url);
			//Create a new DOM document
			$dom = new DOMDocument;
			
			//Parse the HTML. The @ is used to suppress any parsing errors
			//that will be thrown if the $html string isn't valid XHTML.
			@$dom->loadHTML($html);
			
			//Get all links. You could also use any other tag name here,
			//like 'img' or 'table', to extract other tags.
			$links = $dom->getElementsByTagName('a');
			
			//Iterate over the extracted links and display their URLs
			if ($this->checkObject($links))
			{
				// link bucket
				$linkbuket = array();
				
				foreach ($links as $link)
				{
					// get actual link
					$href = $link->getAttribute('href');
					// only use if it meets link convention
					if (strpos($href,'https://www.dropbox.com/sh/') !== false  && strpos($href,$search) !== false && strpos($href,$not) === false)
					{
						if (!in_array($href,$linkbuket))
						{
							// Set link to links array
							$linkbuket[] = $href;
						}
					}
				}
				
				// lift memory burden
				unset($links);
				
				// return found links for this url
				return $linkbuket;
			}
		}
		return false;
	}
	
	protected function checkObject($object)
	{
		if (isset($object) && is_object($object) && count($object) > 0)
		{
			return true;		
		}
		return false;
	}
	
	protected function checkArray($array)
	{
		if (isset($array) && is_array($array) && count($array) > 0)
		{
			return true;
		}
		return false;
	}
	
	protected function checkString($string)
	{
		if (isset($string) && is_string($string) && strlen($string) > 0)
		{
			return true;
		}
		return false;
	}
	
}

// how to use the class (give it the main url that has one set of subfolders)
new DropboxLinks('https://www.dropbox.com/sh/gu2sjrm0wx9lktc/AAA8eEzq3BkxG0UwEHqjAc84a?dl=0');

?>