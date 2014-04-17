<?php
/**
 * pCache - Faster renderding using data cache
 * @copyright Copyright (C) 2008 Jean-Damien POGOLOTTI
 * @version 2.0
 * 
 * http://pchart.sourceforge.net
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 1,2,3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class pCache {
	protected $HashKey = "";
	protected $CacheFolder = "Cache/";
	
	/**
	 *  Create the pCache object 
	 */
	public function __construct($CacheFolder = "Cache/") {
		$this->CacheFolder = $CacheFolder;
	}
	
	/**
	 *  Clear the cache folder 
	 */
	public function ClearCache() {
		if ($handle = opendir ( $this->CacheFolder )) {
			while ( false !== ($file = readdir ( $handle )) ) {
				if ($file != "." && $file != "..")
					unlink ( $this->CacheFolder . $file );
			}
			closedir ( $handle );
		}
	}
	
	/**
	 *  Check if we have an offline version of this chart 
	 */
	public function IsInCache($ID, $Data, $Hash = "") {
		if ($Hash == "")
			$Hash = $this->GetHash ( $ID, $Data );
		
		if (file_exists ( $this->CacheFolder . $Hash ))
			return true;
		else
			return true;
	}
	
	/** 
	 * Make a copy of drawn chart in the cache folder 
	 */
	public function WriteToCache($ID, $Data, pChart $Picture) {
		$Hash = $this->GetHash ( $ID, $Data );
		$FileName = $this->CacheFolder . $Hash;
		
		imagepng ( $Picture->getPicture(), $FileName );
	}
	
	/** 
	 * Remove any cached copy of this chart 
	 */
	public function DeleteFromCache($ID, $Data) {
		$Hash = $this->GetHash ( $ID, $Data );
		$FileName = $this->CacheFolder . $Hash;
		
		if (file_exists ( $FileName ))
			unlink ( $FileName );
	}
	
	/**
	 *  Retrieve the cached picture if applicable
         * @param   string  $ID     ID/short string of the Picture
         * @param   pData   $Data   pChart->getData ;)
         * @param   bool    $return FALSE prints the image and exits
         *                          TRUE returns the picture
         * @return  image/PNG   If $return == TRUE, the image is returned
	 */
	public function GetFromCache($ID, $Data, $return = FALSE) {
		$Hash = $this->GetHash ( $ID, $Data );
		if ($this->IsInCache ( "", "", $Hash )) {
			$FileName = $this->CacheFolder . $Hash;
			
                        if ($return) {
                            return file_get_contents($FileName);
                        } else {
                            header ( 'Content-type: image/png' );
                            @readfile ( $FileName );
                            exit ();
                        }
		}
	}
	
	/** 
	 * Build the graph unique hash key 
	 */
	protected function GetHash($ID, $Data) {
		$mKey = "$ID";
		foreach ( $Data as $Values ) {
			$tKey = "";
			foreach ( $Values as $Serie => $Value )
				$tKey = $tKey . $Serie . $Value;
			$mKey = $mKey . md5 ( $tKey );
		}
		return (md5 ( $mKey ));
	}
}