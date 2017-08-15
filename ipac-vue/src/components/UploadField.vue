<template>
	<div>
		<div class="file-drop">
			<div class="file-label" :class="fileUpload.class">{{ fileUpload.msg }}</div>
			<div class="progress-bar" :style="{width: fileUpload.progress+'%'}"></div>
			<fileupload 
				class="input-file"
				:target="fileUpload.target" 
				action="POST"
				accept=".jpg, .jpeg, .png"
				v-on:start="startUpload"
				v-on:finish="finishUpload"
				v-on:progress="uploadProgress">
			</fileupload>
		</div>
	</div>
</template>

<script type="text/javascript">
	import FileUpload from 'vue-simple-upload/dist/FileUpload'

	export default {
		name: "UploadField",
		props: ["value"],
		components: {
			'fileupload': FileUpload
		},
		data() {
			return {
				activeValue: this.value,
				fileUpload: {
					msg: "Click or drag your file here",
					target: window.apiBase+"auditForm/upload-image?key="+localStorage.apiKey,
					responseFilename: "",
					fileFormat: "",
					progress: 0,
					class: "text-primary"
				},
			}
		},
		methods: {
			startUpload(e) {
				this.fileUpload.msg = "Uploading..."
			},
			finishUpload(e){
				
				let response = JSON.parse(e.target.responseText)

				if (response.status == "success") {
					this.fileUpload.msg = "Upload Complete"
					this.fileUpload.responseFilename = response.filename
					this.fileUpload.submitVisible = true

					this.$emit("uploaded",response)
				}
				else {
					this.fileUpload.msg = response.errors
					this.fileUpload.class = "text-danger"
				}

				let vm = this

				setTimeout(function(){
					vm.fileUpload = {
						msg: "Click or drag your file here",
						progress: 0,
						class: "text-primary",
						width: 0,
						target: window.apiBase+"auditForm/upload-image?key="+localStorage.apiKey,
					}
				},1000)
				
			},
			uploadProgress(e) {
				// file upload progress
				// returns false if progress is not computable
				this.fileUpload.msg = "Uploading... " + e + "%";
				this.fileUpload.progress = e

			},
		}
	}
</script>

<style type="text/css" scoped>
	.btn-group .btn-yes.active {
		background-color: #9ee890;
	}

	.btn-group .btn-no.active {
		background-color: #e68f93;
	}

	.file-drop {
		outline: 2px dashed grey; /* the dash box */
		outline-offset: -10px;
		background: #c5edff;
		color: dimgray;
		padding: 10px 10px;
		min-height: 60px; /* minimum height */
		position: relative;
		cursor: pointer;
		position: relative;
		margin-bottom: 10px;
	}

	.file-drop .file-label {
		position: absolute;
		left: 0;
		top: 20px;
		width: 100%;
		text-align: center;
		z-index: 2;
	}

	.input-file {
		opacity: 0;
		background-color: red;
		height: 100%;
		width: 100%;
		display: block;
		position: absolute;
		top: 0;
		left: 0;
		z-index: 3;
	}

	.progress-bar {
		position: absolute;
		top: 0;
		left: 0;
		background-color: #5bc8f9;
		width: 0%;
		z-index: 1;
	}
</style>