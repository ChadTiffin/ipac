<template>
	<!--<ckeditor 
      v-model="content"
      :config="config" 
      v-on:input="keypress"
      @blur="onBlur($event)" 
      @focus="onFocus($event)">
    </ckeditor>-->
    <textarea style="height: 400px" :id="id" :value="content" v-on:input="input" :name="name" v-html="content"></textarea>
</template>

<script type="text/javascript">

	export default {
		name: "RichText",
		props: ["value","id","name"],
		computed: {
			content() {
				return this.value
			}
		},
		data () {
			return {
				
			}
		},
		methods: {
			input() {
				this.$emit("input",new_value)
			},
		},
		watch: {
			/*value() {
				tinymce.activeEditor.setContent(this.value)
			}*/
		},
		mounted() {
			let vm = this

			tinymce.init({
				selector: '#'+vm.id,
				setup: function(editor) {
					editor.on('keyup',function(e){
						let new_value = editor.getContent()

						vm.$emit("input",new_value)
					});
					editor.on('init',function(){
						
						if (vm.value != null)
							this.setContent(vm.value)
						else 
							this.setContent("")

					});
					editor.on('change undo redo paste keypress',function(){
						vm.$emit("input",editor.getContent())
						console.log("change")
					});

				},
				plugins: 'link lists advlist hr code',
				menubar : false,
				toolbar: 'undo redo | alignleft aligncenter alignright | bold italic bullist numlist hr | code',
				formats: {
					custom_format: {block : 'p', styles : {margin: '0px'}}
				}
			});

		},
		beforeDestroy () {

			//kill the editors DEAD!
			tinymce.remove("#"+this.id)
			tinymce.EditorManager.execCommand('mceRemoveControl',true, this.id);
			tinymce.EditorManager.editors = [];
		}
	}
</script>