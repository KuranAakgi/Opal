<?php
class GeneralController{
	
	//disable instance initiation
    private function __construct() {
    }
	
	public static function requireVarialbe($variable, $scope = 'request', &$resultArray = 'GLOBALS'){
		$scope = strtolower ($scope);
		if($resultArray == 'GLOBALS'){
		 	$resultArray = $GLOBALS;
		}
		
		switch($scope){
		case 'request':
			$scopeObject = $_REQUEST;
			break;
		case 'get':
			$scopeObject = $_GET;
			break;
		case 'post':
			$scopeObject = $_POST;
			break;
		}
		
		
		foreach($variable as $key=>$v){
			if(!isset($array[$key])){
				$resultArray[$key] = isset($scopeObject[$key])?$scopeObject[$key]:$v;
				if(isset($scopeObject[$key])){
					$resultArray[$key] = $scopeObject[$key];
				} else{
					if(isset($v)){
						$resultArray[$key] = $v;
					} else{
						die("missing parameter $key");
					}
				}
			}
		}
	}
	
	public static function requireFiles($fileKeys, $fileSavingDirectory = './uploadedFile', &$resultArray = 'GLOBALS'){
		
		//formalize real path of file saving directory, remove all '/' or '\\' at the end of realPathOfFileSavingDirectory
		$fileSavingDirectory = rtrim($fileSavingDirectory, '/' );
		$fileSavingDirectory = rtrim($fileSavingDirectory, '\\' );
		
		//formalize real path of file saving directory, replace incorrect directory separator
		if(DIRECTORY_SEPARATOR == '\\'){
			$fileSavingDirectory = str_replace('/', DIRECTORY_SEPARATOR, $fileSavingDirectory);
		}else {
			$fileSavingDirectory = str_replace('\\', DIRECTORY_SEPARATOR, $fileSavingDirectory);
		}
		
		//check directory existence
		$realPathOfFileSavingDirectory = realpath($fileSavingDirectory);
		if(!is_dir($realPathOfFileSavingDirectory)){
			throw new Exception('fileSavingDirectory not exists');
			exit();
		}
		
		if($resultArray == 'GLOBALS'){
		 	$resultArray = $GLOBALS;
		}
		
		$realPathOfFileSavingDirectory .= DIRECTORY_SEPARATOR ;
		$fileSavingDirectory .= DIRECTORY_SEPARATOR ;
		
		foreach($fileKeys as $fileKey){
			//get sub file name
			$subFileName = substr($_FILES[$fileKey]['name'], strrpos($_FILES[$fileKey]['name'], '.'));
			
			//random a new file name, which is not exist, for the uploaded file.
			srand((double) microtime() * 1000000);
			do{
				$randomFileName = md5(rand(10000000, 99999999)).$subFileName;
				$randomFileRealPath = $realPathOfFileSavingDirectory.$randomFileName;
			} while(file_exists($randomFileRealPath));
			
			//add to array with relative path of the file
			$resultArray[$fileKey] = $fileSavingDirectory.$randomFileName;		
				
			//move file to the directory
			move_uploaded_file($_FILES[$fileKey]['tmp_name'], $randomFileRealPath);
		}
	}
	
	public static function stringArrayReplace($source, $arrayToReplace){
		$result = $source;
		foreach($arrayToReplace as $key => $value){
			$result = str_replace($key, $value, $result);
		}
		return $result;
	}
}
