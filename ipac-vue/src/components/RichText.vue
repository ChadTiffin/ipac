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
				initialized: false
			}
		},
		methods: {
			input() {
				this.$emit("input",editor.getContent())
			},
			initEditor() {
				this.initialized = true
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
						editor.on('change undo redo paste keydown',function(){
							vm.$emit("input",editor.getContent())
						});

					},
					plugins: 'link lists advlist hr code',
					menubar : false,
					toolbar: 'undo redo | alignleft aligncenter alignright | bold italic bullist numlist hr | code',
					formats: {
						custom_format: {block : 'p', styles : {margin: '0px'}}
					}
				});
			}
		},
		watch: {
			value() {
				this.initEditor()
				if (!this.initialized) {
					tinymce.activeEditor.setContent(this.value)
					this.initialized = true
				}
				
			}
		},
		mounted() {
			let vm = this

			//fallback if it takes too long to receive value property change (assume value is blank)
			setTimeout(function(){
				if (!vm.initialized)
					vm.initEditor()
			},500)

		},
		beforeDestroy () {

			//kill the editors DEAD!
			tinymce.remove("#"+this.id)
			tinymce.EditorManager.execCommand('mceRemoveControl',true, this.id);
			tinymce.EditorManager.editors = [];
		}
	}
</script>