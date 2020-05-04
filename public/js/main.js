(function(ctx,$){

	var app = {
		partOfPath : [],
		path : "",

		init : function(){
			var homeUrl = 'http://localhost:8000/home';
			if ( document.URL == homeUrl ){
			    sessionStorage.clear();
			}
			if(sessionStorage.getItem('prevPath')){
				self.partOfPath =sessionStorage.getItem('prevPath').split(',');
				

			}
			this.returnPrev();
			
			this.deleteFolder();
			this.deleteFiles();
			this.addFolder();
			this.switchFolder();
			this.zipify();
		},
		returnPrev : function(){
			var $btn = $('.return-btn');
			var $form = $('.uploaded-form');
			$btn.on('click',function(evt){
				evt.preventDefault();


				self.partOfPath.pop()
				
				self.path = self.partOfPath.toString();
				self.path = self.path.replace(new RegExp(',', 'g'),'/');
				sessionStorage.setItem('path', self.path);
				sessionStorage.setItem('prevPath', self.partOfPath);
				$form.append("<input type='hidden' name='path' value='"+self.path+"'>")
				var lastFolder= self.partOfPath.pop();
				if( lastFolder !== undefined){

					$form.attr('action','/uploads/'+lastFolder+'/folder' );
				}
				else{
					$form.attr('action','/home' );
				}
				$form.submit();

				
			});
		},
		initSession	: function(folder){
			var $name = $('.dropdown-toggle')[0].innerText;
			
			
			sessionStorage.setItem('name', $name);
			sessionStorage.setItem('prevPath',"");
	
			var $prevPath = $(folder).attr('data-name-folder');
			self.partOfPath.push($prevPath);
			
			sessionStorage.setItem('prevPath', self.partOfPath);
			self.path = self.partOfPath.toString();
			self.path = self.path.replace(new RegExp(',', 'g'),'/');
			sessionStorage.setItem('path', self.path);

		},
		deleteFolder : function(){
			var $checked = $('.check-folder');
			var $delete = $('.delete');
			var $form = $('.uploaded-form');

			$checked.on('change', function(evt){
				
				var folder = {};
				var $this = $(this);
				folder.url = $this.attr('data-url');
				folder.id = $this.attr('data-id');

				if($this[0].checked === true){

					if($($this.next()).attr('class') === 'readyToSend'){
						$this.next().next().remove();
						$this.next().remove();
					}
					$this.after("<input class='readyToSend' name='url-folder[]' type='hidden' value="+folder.url+">");
					$this.after("<input class='readyToSend' name='id-folder[]' type='hidden' value="+folder.id+">")
				}
				else{
					if($($this.next()).attr('class') === 'readyToSend'){
						$this.next().next().remove();
						$this.next().remove();
					}

				}
			});
			$delete.on('click', function(evt){
				evt.preventDefault();

			
				$form.attr('action','/delete');
				$form.submit();
			});
		},

		deleteFiles : function(){
			var $checked = $('.check');
			var $delete = $('.delete');
			var $form = $('.uploaded-form');
			
			$checked.on('change', function(evt){

				
				var file = {};
				var $this = $(this);
				file.url = $this.attr('data-url');
				file.id = $this.attr('data-id');

				if($this[0].checked === true){
					if($($this.next()).attr('class') === 'readyToSend'){
						$this.next().next().remove();
						$this.next().remove();
					}
					$this.after("<input class='readyToSend' name='url[]' type='hidden' value="+file.url+">");
					$this.after("<input class='readyToSend' name='id[]' type='hidden' value="+file.id+">")
				}
				else{
					if($($this.next()).attr('class') === 'readyToSend'){
						$this.next().next().remove();
						$this.next().remove();
					}

				}
			});
			$delete.on('click', function(evt){
				evt.preventDefault();

			
				$form.attr('action','/delete');
				$form.submit();
			});

			
		},
		switchFolder : function(){
			var $folder = $('.folder');
			var $id = null;
			var $nameFolder = null;


			$folder.on('click', function(evt){
				evt.preventDefault();
				self.initSession(this);
				$id= $(this).attr('data-id-folder');
				$nameFolder = $(this).attr('data-name-folder');

				if($(this).attr('href') === 'uploads/'+$nameFolder){
					evt.preventDefault();
				
					$('.panel-body').append("<ul class='file-list'></ul>")
					$('.uploaded-form').attr(('action'),'/uploads/'+$nameFolder+'/folder');//ajoute un nouveau dossier
					
					$('#upload-form').append("<input class='folder-id' type='hidden' name='folder_id' value="+$id+">")
					$('.uploaded-form').append("<input class='folder-id' type='hidden' name='folder_id' value="+$id+">");
					
					$('.uploaded-form').submit();

				}
				
			});

		},
		addFolder : function(){
			var $add = $('.add-folder');
			var $form = $('.uploaded-form');

			$add.on('click', function(evt){
				evt.preventDefault();
				$form.attr('action','/addFolder');
				$('.add-folder-form').remove();
				$form.prepend("<div class='add-folder-form'></div>");
				$('.add-folder-form').append("<h2>Name your folder</h2><input type='text' class='form-control add' placeholder='name of folder' name='addFolder'>");
		
				$('.add').after("<button type= 'submit' class='btn btn-success add-button' >add</button>")


			});
		},
		zipify : function(){

			var $folderDown = $('.folder-down');
			var $form = $('.uploaded-form');

			$folderDown.on('click', function(evt){
				evt.preventDefault();
				var $nameFolder = $(this).attr('data-name-folder');
				$form.attr('action','/download/'+$nameFolder+'/zipify');
				$form.submit();
			});
		}

	}
	ctx.app = app
	var self = app

})(window, jQuery);
