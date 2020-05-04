<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use ZipArchive;
use Response;
use File;
class HomeController extends Controller
{
	/**
	* Create a new controller instance.
	*
	* @return void
	*/
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	* Show the application dashboard.
	*
	* @return \Illuminate\Http\Response
	*/
	public function index(Request $request)
	{

		$session = $request->session();
		$session->put('folder_open', false);
		$session->put('folder_id', null);
		$session->put('prevPath', null);


		$files = $this->getFiles();
		$folders = $this->getFolders();
		File::deleteDirectory(public_path().'/downloads');
		return view('home',['files'=>$files, 'folders'=>$folders,'opened'=>$session->get('folder_open'),'prevPath'=>$session->get('prevPath')]);
		
	}
	public function getFileInFolder(Request $request, $folder)
	{
		$session = $request->session();
		$session->put('folder_open', true);
		$folders = Db::table('folders')->select('*')->where('name', '=',$folder)->get();
		$session->put('folder_id', $folders[0]->id );
		$currentFolder = $session->get('prevPath');
		$pageWasRefreshed = isset($_SERVER['HTTP_CACHE_CONTROL']) && $_SERVER['HTTP_CACHE_CONTROL'] === 'max-age=0';

		

		if($currentFolder === null){ //if we are in root
			$session->put('prevPath', $folders[0]->name);
			$session->put('root_id',$folders[0]->id);
		}
		else{
			if($pageWasRefreshed ) {
			
			//do something because page was refreshed;
			
			
			} 
			else {

			
				
				$prevpath = $currentFolder.'/'.$folders[0]->name;

				
				$session->put('prevPath', $prevpath);
			}
		}

		if($request->input('path')!==null){
			$session->put('prevPath',$request->input('path') );
		}
		$subFolders = Db::table('folders')->select('*')->where('folder_id','=',$folders[0]->id)->get();



		$files = Db::table('files')->select('*')->where('folder_id', '=',$folders[0]->id)->get();


		return view('home',['files'=>$files, 'folders'=>$folders, 'subFolders'=>$subFolders,'opened'=>$session->get('folder_open'),'prevPath'=>$session->get('prevPath')]);
	}
	public function getFiles()
	{
		$files = DB::table('files')->select('*')->where('folder_id','=',null)->get();
		return $files;
	}
	public function getFolders(){
		$folders = DB::table('folders')->select('*')->where('folder_id','=',null)->get();
		return $folders;
	}
	public function upload(Request $request)
	{

		$files = $request->file('upload_file');
		$session = $request->session();
		$folderId = $session->get('folder_id');
		$opened = $session->get('folder_open');

		if($folderId == null && $opened === false){// we are in root

			foreach ($files as $file) {
				$name  =  $file->getClientOriginalName();
				$size = $file->getClientSize();
				$url = str_random(40).'.'.$file->getClientOriginalExtension();

				Db::table('files')->insert([
				"name" => $name,
				"url" => $url,
				"size" => $size,

				]);

				$file->move(public_path().'/uploads/',$url);
			}
		}
		else{

			$folder = Db::table('folders')->select('name')->where('id','=',$folderId)->get();
			$prevPath = $session->get('prevPath').'/';

			foreach ($files as $file) {
				$name  =  $file->getClientOriginalName();
				$size = $file->getClientSize();
				$url = str_random(40).'.'.$file->getClientOriginalExtension();

				Db::table('files')->insert([
				"name" => $name,
				"url" => $prevPath.$url,
				"size" => $size,
				"folder_id"=> $folderId
				]);

				$file->move(public_path().'/uploads/'.$prevPath,$url);
			}



		}
		return redirect('/home');
	}
	public function deleteFile(Request $request)
	{
		$files = $request->input();
		$session = $request->session();

		$opened =  $session->get('folder_open');
		$prevpath = $session->get('prevPath');
		$folderId = $session->get('folder_id');


		if(isset($files['url-folder'])  && isset($files['id-folder'])  ){
			$urlFolders = $files['url-folder'] ;

			$idFolders =  $files['id-folder'];
			if($opened !== false){


				for($i = 0; $i<count($idFolders); $i++){


				File::deleteDirectory(public_path().'/uploads/'.$prevpath.'/'.$urlFolders[$i]);
				}
			}

			for($i = 0; $i<count($idFolders); $i++){
				Db::table('folders')->where('folder_id','=',$idFolders[$i])->delete();
				Db::table('folders')->where('id','=',$idFolders[$i])->delete();
				File::deleteDirectory(public_path().'/uploads/'.$urlFolders[$i]);


			}
		}

		if(isset($files['url'])  && isset($files['id'])){       
			$urls = $files['url'];
			$ids = $files['id'];


			if($opened !== false){
				$folder = Db::table('folders')->select('name')->where('id','=',$folderId)->get();
				for($i = 0; $i<count($ids); $i++){
					File::delete(public_path().'/uploads/'.$folder[0]->name.'/'.$urls[$i]);
				}
			}

			for($i = 0; $i<count($ids); $i++){

				Db::table('files')->where('id','=',$ids[$i])->delete();
				File::delete(public_path()."/uploads/".$urls[$i]);

			}
		}

		return redirect('/home');
	}
	public function addFolder(Request $request)
	{
		$folderName = $request->input('addFolder');
		$session = $request->session();
		$prevPath = $session->get('prevPath');
		$opened = $session->get('foler_open');
		$folder_id = $session->get('folder_id');
		$root_id = $session->get('root_id');

		if($opened === false && $folder_id !== null) // if we are in root
		{

			Db::table('folders')->insert([
				'name'=>$folderName,
				'folder_id' =>$folder_id,
				'root_id' =>0
			]);
			File::makeDirectory(public_path()."/uploads/".$folderName,0775,true);
		}
		else{// if we are in subfolder


			Db::table('folders')->insert([
				'name'=>$folderName,
				'folder_id' =>$folder_id,
				'root_id' =>$root_id
			]);
			File::makeDirectory(public_path()."/uploads/".$prevPath.'/'.$folderName,0775,true);
		}
		return redirect('/home');
	}
	public function zipify(Request $request, $folderName)
	{
		File::makeDirectory(public_path().'/downloads');
		$session = $request->session();
		$prevPath = $session->get('prevPath');
		$zipFileName = $folderName.'.zip';
		
		if($session->get('folder_open') === false){ //if we are in root
			$dirName = public_path().'/uploads/'.$prevPath;
		}
		else{
			$dirName = public_path().'/uploads/'.$prevPath.'/'.$folderName;
		}

		// Get real path for our folder
		$rootPath = realpath($dirName);


		// Initialize archive object
		$zip = new ZipArchive();
		$zip->open(public_path().'/uploads/'.$zipFileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($rootPath),
				RecursiveIteratorIterator::LEAVES_ONLY
			);

		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);

				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}

		// Zip archive will be created only after closing object
		$zip->close();
		File::move(public_path()."/uploads/" .$zipFileName, public_path()."/downloads/".$zipFileName);
		$headers = array(
		'Content-Type' => 'application/octet-stream',
		);

		// Download .zip file.
		return Response::download( public_path() . '/downloads/' . $zipFileName, $zipFileName, $headers );
	}
}
