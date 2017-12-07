<template>
	<!--<ckeditor 
      v-model="content"
      :config="config" 
      v-on:input="keypress"
      @blur="onBlur($event)" 
      @focus="onFocus($event)">
    </ckeditor>-->

    <textarea style="height: 400px" :id="id" v-on:input="input" :name="name"></textarea>

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
				initialized: false,
				localValue: this.value,
				lastValue: null
			}
		},
		methods: {
			input() {
				this.$emit("input",editor.getContent())
			},
			initEditor() {
				
				let vm = this

				tinymce.remove("#"+this.id)
				tinymce.init({
					selector: '#'+vm.id,
					setup: function(editor) {
						/*editor.on('keyup',function(e){
							//let new_value = editor.getContent()
							let container = editor.getContentAreaContainer();

							let html = container.querySelector("iframe").contentWindow.document.body.innerHTML

							vm.$emit("input",html)
						});*/
						editor.on('init',function(){
							if (vm.value != null) 
								this.setContent(vm.value)
							
							else 
								this.setContent("")

						});
						editor.on('change undo redo paste keyup',function(){
							let container = editor.getContentAreaContainer();

							let html = container.querySelector("iframe").contentWindow.document.body.innerHTML

							vm.$emit("input", html)
						});

					},
					plugins: 'link lists advlist hr code',
					menubar : false,
					toolbar: 'undo redo | alignleft aligncenter alignright | bold italic bullist numlist hr | code',
					formats: {
						custom_format: {block : 'p', styles : {margin: '0px'}}
					}
				});
				this.initialized = true
			}
		},
		watch: {
			value() {

				if (!this.initialized) {
					this.initEditor()

					if (tinymce.get(this.id))
						tinymce.get(this.id).setContent(this.content)

				}

				if (!this.lastValue && this.lastValue != "" && this.initialized) {
					if (tinymce.get(this.id))
						tinymce.get(this.id).setContent(this.value)
				}

				this.lastValue = this.content
			}
		},
		mounted() {
			let vm = this

			if (!vm.initialized) {
				vm.initEditor()
			}

			//fallback if it takes too long to receive value property change (assume value is blank)
			setTimeout(function(){
				if (!vm.initialized) {
					vm.initEditor()
				}
			},750)

		},
		beforeDestroy () {

			//kill the editors DEAD!
			this.initialized = false
			tinymce.remove("#"+this.id)
			tinymce.EditorManager.execCommand('mceRemoveControl',true, this.id);
			tinymce.EditorManager.editors = [];
		}
	}
</script>