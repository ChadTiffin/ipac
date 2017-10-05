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

		<div class="thumbs" v-if="!noThumbs">
			<div 
				v-for="(image,index) in thumbsWithTokens" 
				class="thumb" 
				:style="{backgroundImage: 'url('+apiBase+'image/serve/'+image+'&t='+rand+')'}">
				<!--<p style="margin-top: 40px;">{{ image }}</p>-->
				<div class="buttons">

					<!--<button type="button" class="btn btn-sm btn-default" v-on:click="showLightbox(image)"><i class="fa fa-arrows-alt"></i></button>-->

					<button type="button" class="btn btn-default btn-sm" v-clipboard:copy="copyImageUrl(image)"><i class="fa fa-copy"></i></button>
				
					<button type="button" class="btn btn-default btn-sm" v-on:click="rotate('left',index, images)">
						<i class="fa fa-rotate-left"></i>
					</button>

					<button type="button" class="btn btn-default btn-sm" v-on:click="rotate('right',index, images)">
						<i class="fa fa-rotate-right"></i>
					</button>

					<button type="button" class="btn btn-danger btn-sm" v-on:click="deleteImage(index, images)">
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
				thumbsWithTokens: []
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
							let token = "none"
							if (image.token)
								token = image.token

							console.log(typeof merged)

							merged.push(image.filename+"?token="+token);
						})

						vm.thumbsWithTokens = merged;
						
					})
				}
				else 
					vm.thumbsWithTokens = []
			},
			saveUpload(response) {

				this.$emit("imageListChanged",response,this.images,"addition")

			},
			showLightbox(image) {
				this.lightboxSrc = this.apiBase+'image/serve/'+image+'&t='+this.rand
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
			deleteImage(index,images) {
				let payload = {
					filename: images[index]
				}

				let vm = this

				this.postData(this.apiBase+"image/delete",payload).then(function(response){
					if (response.status == "success") {

						let new_images = []
						images.forEach(function(image, arr_index){
							if (index != arr_index)
								new_images.push(image)
						})

						vm.$emit("imageListChanged",response,new_images,"deletion")

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