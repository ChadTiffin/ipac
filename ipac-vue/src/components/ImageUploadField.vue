<template>
	<div>
		<modal-dialog
            v-if="lightboxVisible" 
            title="Viewer" 
            :modal-visible="lightboxVisible" 
            :submit-visible="false"
            v-on:closeModal="lightboxVisible = false">

			<img :src="lightboxSrc">

		</modal-dialog>

		<modal-dialog
            v-if="deleteDialog.visible" 
            title="Delete or Remove?" 
            :modal-visible="deleteDialog.visible" 
            :submit-visible="true"
            confirm-button-text="Remove Only"
            button-class="btn-warning"
            v-on:confirm="removeImage"
            v-on:closeModal="deleteDialog.visible = false">

			Would you like to delete this image or just remove it from this list?

			<button slot="footer" type="button" class="btn btn-danger" v-on:click="confirmDeleteImage"><i class="fa fa-remove"></i> Delete</button>

		</modal-dialog>

		<div class="thumbs" v-if="!noThumbs">
			<div 
				v-for="(image,index) in thumbsWithTokens" 
				class="thumb" 
				:style="{backgroundImage: 'url('+apiBase+'image/serve/'+image+'?t='+rand+')'}">
				<!--<p style="margin-top: 40px;">{{ image }}</p>-->
				<div class="buttons">

					<!--<button type="button" class="btn btn-sm btn-default" v-on:click="showLightbox(image)"><i class="fa fa-arrows-alt"></i></button>-->

					<button title="Copy image url to clipboard" type="button" class="btn btn-default btn-sm" v-clipboard:copy="copyImageUrl(image)"><i class="fa fa-copy"></i></button>
				
					<button type="button" class="btn btn-default btn-sm" v-on:click="rotate('left',index, images)">
						<i class="fa fa-rotate-left"></i>
					</button>

					<button type="button" class="btn btn-default btn-sm" v-on:click="rotate('right',index, images)">
						<i class="fa fa-rotate-right"></i>
					</button>

					<button type="button" class="btn btn-danger btn-sm" v-on:click="deleteImage(index)">
						<i class="fa fa-remove"></i>
					</button>

				</div>

			</div>
		</div>

		<upload-field v-if="!multi && images.length == 0 || multi" :upload-type="uploadType" v-on:uploaded="saveUpload($event)" :upload-msg="uploadMsg"></upload-field>
	</div>

</template>

<script type="text/javascript">
	import UploadField from './UploadField'
	import ModalDialog from './ModalDialog'

	export default {
		name: "ImageUploadField",
		props: ["images", "apiBase","multi","uploadMsg","uploadType","noThumbs"],
		components: {
			UploadField,
			ModalDialog
		},
		data() {
			return {
				rand: Math.random(),
				lightboxSrc: "",
				lightboxVisible: false,
				thumbsWithTokens: [],
				deleteDialog: {
					visible: false,
					index: null
				}
			}
		},
		watch: {
			images() {
				this.getImageTokens();
			}
		},
		methods: {
			copyImageUrl(image) {
				let filename = image.split("?")[0]

				return window.apiBase+"image/serve/"+filename
			},
			getImageTokens() {
				let vm = this

				if (this.images && this.images.length > 0) {
					let images = JSON.stringify(this.images);

					this.getJSON(window.apiBase+"image/get-image-tokens?images="+images).then(function(response){
						let merged = []

						Array.from(response).forEach(function(image, index) { 
							let token = ""
							if (image.token)
								token = image.token+"/"
							else
								token = "public/"

							merged.push(token+image.filename);
						})

						vm.thumbsWithTokens = merged;
						
					})
				}
				else 
					vm.thumbsWithTokens = []
			},
			saveUpload(response) {

				let params = {
					response: response,
					new_images: [response.filename],
					type: 'addition'
				}

				this.$emit("imageListChanged",params)

			},
			showLightbox(image) {
				this.lightboxSrc = this.apiBase+'image/serve/'+image+'?t='+this.rand
				this.lightboxVisible = true
			},
			rotate(direction, index, images) {
				let payload = {
					filename: images[index],
					direction: direction
				}

				let vm = this

				this.postData(this.apiBase+"image/rotate",payload).then(function(response){
					vm.rand = Math.random()
				})
			},
			deleteImage(index, images) {
				this.deleteDialog = {
					visible: true,
					index: index
				}
			},
			removeImage() {

				let vm = this
				this.deleteDialog.visible = false

				let new_images = []
				let removed_image = ""

				vm.images.forEach(function(image, arr_index){
					if (vm.deleteDialog.index != arr_index)
						new_images.push(image)
					else
						removed_image = vm.images[arr_index]
				})

				let params = {
					response: "",
					new_images: new_images,
					removed_image: removed_image,
					type: 'deletion'
				}

				vm.$emit("imageListChanged",params)

			},
			confirmDeleteImage() {
				let payload = {
					filename: this.images[this.deleteDialog.index]
				}

				let vm = this

				this.deleteDialog.visible = false

				this.postData(this.apiBase+"image/delete",payload).then(function(response){
					if (response.status == "success") {

						let new_images = []
						vm.images.forEach(function(image, arr_index){
							if (vm.deleteDialog.index != arr_index)
								new_images.push(image)
						})

						let params = {
							response: response,
							new_images: new_images,
							type: 'deletion'
						}

						vm.$emit("imageListChanged",params)

					}

					
				})
			},
		},
		created() {
			this.getImageTokens();
		}
	}
</script>

<style type="text/css" scoped>
	.thumbs .thumb {
		display: inline-block;
		height: 120px;
		width: 150px;
		background-size: cover;
		background-position: center center;
		background-repeat: no-repeat;
		position: relative;
	}

	.thumbs .thumb .buttons {
		position: absolute;
		top: 0;
		right: 0;
	}

	.lightbox {
		position: fixed;
		width: 640px;
		margin:auto;
		top: calc(50% - 320px);
		left: calc(50% - 240px);
		z-index: 10;
		box-shadow: 0 0 20px 5px black;
		text-align: center;
		background-color: black;
	}

</style>