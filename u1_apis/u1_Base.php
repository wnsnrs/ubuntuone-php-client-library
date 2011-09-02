<?php

/*

 * APIs functions are found all in class 'UbuntuOneAPIs' here below.
 * You can find a more detailed documentation in the wiki: https://github.com/paglias/ubuntuone-php-client-library/wiki/
 * 
 * Except where noted the request url MUST NOT start with a /.
 * If one functions fails due to an OAuth error the error text will be stored in $this->error_message and 
 * the code error (for example 404) will be stored in $this->error_code. 
 * 

*/
class UbuntuOneAPIs{
	
	public $files_api_url = 'https://one.ubuntu.com/api/file_storage/v1/'; //url for files API
	public $account_info_api_url = 'https://one.ubuntu.com/api/account/'; //url for account API
	public $files_content_url = 'https://files.one.ubuntu.com/content/'; //url to get files content - it should be fixed in the future from APIs maintainers
	public $base_path = '~/Ubuntu%20One/'; //base path for requests to files or folders
	public $volumes_path = 'volumes'; //url for volumes - volumes aren't still supported but i have it in my todo list
	public $volumes_base_path = 'volumes/~/'; //base path for a single volume
	
	public $conskey;
	public $conssec;
	public $token;
	public $token_secret;
	
	public $oauth;
	
	public $error_code; //this var will contain the error code when you find one usign the apis (like a 404)
	public $error_message; //this will contain the error message like ({error: not found})
	
	public $connection; //this var will be set to TRUE if the connection to apis work else it will be set to FALSE
	
	/*
	 * Construct function which require 4 parameters: token, token secret, consumer key and consumer secret.
	 * It is used to set the connection to Ubuntu One using OAuth.
	 * It make a simple request to apis to try if the connection worksM; if successful the var $this->connection will be set to TRUE otherwise it will be set to FALSE 
	 * and infos about the error will be stored in the variables $this->error_code and $this->error_message. 
	*/
	
	public function __construct($conskey, $conssec, $token, $token_secret){
		
		$this->conskey = $conskey;
		$this->conssec = $conssec;
		$this->token = $token;
		$this->token_secret = $token_secret;
		
		try{
			
		$this->oauth = new OAuth($this->conskey,$this->conssec,OAUTH_SIG_METHOD_HMACSHA1,OAUTH_AUTH_TYPE_URI);
				
		$this->oauth->enableDebug();
		$this->oauth->enableSSLChecks();
		$this->oauth->setToken($this->token,$this->token_secret);
		
		$this->oauth->fetch($this->account_info_api_url); //make a simple request to try the connection
		
		$this->connection = true;
		
		} catch (OAuthException $E) {
						
			$this->error_message = $E->lastResponse;
			
			$this->error_code = $E->getCode();
			
			$this->connection = false;
												
			}
				
		}
		
   /*
    * Function to get info about user account.
    * It returns an array and doesn't request any parameter.
    * If it fails it return false.
   */ 
   
	
	public function getAccountInfo(){
		
		try {
			
		$this->oauth->fetch($this->account_info_api_url);
		
		$infoAccount = $this->oauth->getLastResponse(); 
		
		return json_decode($infoAccount, 1); 
			
		}catch(OAuthException $E) {		
			
			$this->error_message = $E->lastResponse;
			
			$this->error_code = $E->getCode();
			
			return false;
							
			}
		
		}
	
   /*
    * Function to get info about user space on Ubuntu One like space used....
    * It returns an array and doesn't request any parameter.
    * If it fails it return false.
   */ 
   
	public function getRootInfo(){
		
		$new_file_api_url = substr($this->files_api_url, 0, -1);
		
		try {
		
		$this->oauth->fetch($new_file_api_url);
		
		$infoRoot = $this->oauth->getLastResponse();
			
		return json_decode($infoRoot, 1);
		
		}catch(OAuthException $E) {		
			
			$this->error_message = $E->lastResponse;
			
			$this->error_code = $E->getCode();
			
			return false;
							
			}
		
		}

   /*
    * Function to get info about a file or a folder.
    * It returns an array and request 2 parameters:
    * item path and if you want info about item children (only for folders)
    * It return an array if it works and false if it fails.
   */ 
   
	public function getItemInfo($path, $children = false){ 
		
		try {
		
			$include = "";
		
			if($children){			
				$include = '?include_children=true';
				}
			
			$path_e = str_replace(' ','%20',$path); 
							
			$this->oauth->fetch($this->files_api_url.$this->base_path.$path_e.$include);
			
			$infoItem = $this->oauth->getLastResponse();
		
			return json_decode($infoItem, 1);

						
		}catch(OAuthException $E) {
			
			$this->error_message = $E->lastResponse;

			$this->error_code = $E->getCode();
			
			return false;
			
			}
				
		}
		
   /*
    * Function to create an empty folder or file.
    * It request type (file or folder) and new item path.
    * TO FIX: files created using this function will exist but won't be shown in user account. 
    * To create a file with some content see putFilecontent() below.
    * It return true if it works and false if it fails.
   */ 
	
	public function putEmptyItem($type, $path){ 
								
		$kind = array(
		'kind' => $type
		);
			
		$kind_e = json_encode($kind);
		
		try {
					
			$this->oauth->fetch($this->files_api_url.$this->base_path.$path, $kind_e, OAUTH_HTTP_METHOD_PUT);
			
			return true;

		}catch(OAuthException $E) {		
			
			$this->error_message = $E->lastResponse;
			
			$this->error_code = $E->getCode();
			
			return false;
							
			}
			
			
				
		}
	
   /*
    * Function to publish a file.
    * It request path of the file to publish. 
    * If you want to un-publish set a second parameter to 'false'.
    * It return true if it works and false if it fails.
   */
   
    	
	public function publishItem($path, $publish = true){
		
		$path_e = rawurlencode($path);
		
		try {
	
		$this->oauth->fetch($this->files_api_url.$this->base_path.$path_e);
		
		$response_d = json_decode($this->oauth->getLastResponse());
		
		$response_d->is_public = $publish;
		
		$response_e = json_encode($response_d);
		
		}catch(OAuthException $E) {		
			
			$this->error_message = $E->lastResponse;
			
			$this->error_code = $E->getCode();
			
			return false;
							
			}
		
		try{
			
		$this->oauth->fetch($this->files_api_url.$this->base_path.$path_e, $response_e, OAUTH_HTTP_METHOD_PUT);
		
		return true;
		
		}catch(OAuthException $E) {		
			
			$this->error_message = $E->lastResponse;
			
			$this->error_code = $E->getCode();
			
			return false;
							
			}
		
		}
		
   /*
    * Function to move/rename an item.
    * It request actual path of the item and its new path (new path MUST starts with a /). 
    * Parent folders MUST exist and won't be created.
    * It return true if it works and false if it fails.
   */
	public function moveItem($path, $new_path){
	
		$path_e = rawurlencode($path);
		
		try {
	
		$this->oauth->fetch($this->files_api_url.$this->base_path.$path_e);
				
		$response_d = json_decode($this->oauth->getLastResponse());
		
		$response_d->path = $new_path;
		
		$response_e = json_encode($response_d);
		
		}catch(OAuthException $E) {		
			
			$this->error_message = $E->lastResponse;
			
			$this->error_code = $E->getCode();
			
			return false;
							
			}
			
		try{
		
		$this->oauth->fetch($this->files_api_url.$this->base_path.$path_e, $response_e, OAUTH_HTTP_METHOD_PUT);
		
		return true;
		
		}catch(OAuthException $E) {		
			
			$this->error_message = $E->lastResponse;
			
			$this->error_code = $E->getCode();
			
			return false;
							
			}		
		}
	
   /*
    * Function to delete and item.
    * It request path of the item to delete.
    * It return true if it works and false if it fails.
   */
   	
	public function deleteItem($path){
		
		$path_e = rawurlencode($path);
		
		try {
		
		$this->oauth->fetch($this->files_api_url.$this->base_path.$path_e, " " ,OAUTH_HTTP_METHOD_DELETE);
		
		return true;
		
		}catch(OAuthException $E) {		
			
			$this->error_message = $E->lastResponse;
			
			$this->error_code = $E->getCode();
			
			return false;
							
			}
		
		}
	
   /*
    * Function to get file content.
    * It request path of the file. 
    * It return a string if it works, false if it fails.
   */
   
	public function getFileContent($path){
		
		$path_e = rawurlencode($path);
		
		try {
		
		$this->oauth->fetch($this->files_content_url.$this->base_path.$path_e);
				
		$itemBody = $this->oauth->getLastResponse();
		
		return $itemBody;
		
		}catch(OAuthException $E) {		
			
			$this->error_message = $E->lastResponse;
			
			$this->error_code = $E->getCode();
			
			return false;
							
			}
		}
		
   /*
    * Function to put some content. to a file or create a new file with some contents.
    * It request path of the file and the content you want to put like a string.
    * It return true if it works and false if it fails. 
   */
   
	public function putFileContent($path, $file_content){
		
		$path_e = rawurlencode($path);
		
		try {
		
		$this->oauth->fetch($this->files_content_url.$this->base_path.$path_e, $file_content, OAUTH_HTTP_METHOD_PUT); 
		
		return true;
		
		}catch(OAuthException $E) {		
			
			$this->error_message = $E->lastResponse;
			
			$this->error_code = $E->getCode();
			
			return false;
							
			}
		
		}
		
	}

?>

