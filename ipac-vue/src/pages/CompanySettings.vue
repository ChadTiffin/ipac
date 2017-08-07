<template>
	<section>
		<p>These will be available in templates as the following variables:</p>
		<form class="form-horizontal" v-on:submit.prevent="save">
			<table class="table table-striped">
				<tbody>
					<tr v-for="setting in renderedSettings">
						<td style="width: 200px;">company.{{ setting.setting }}</td>
						<td>
							<form-group :label="setting.label" col-class="col-md-3">
								<select v-if="setting.type == 'select'" v-model="setting.value" class="form-control">
									<option v-for="option in options">{{option}}</option>
								</select>
								<input v-else class="form-control" type="text" v-model="setting.value">
							</form-group>
						</td>
					</tr>
				</tbody>
				<tfoot>
					<td colspan="2">
						<button class="btn btn-success pull-right"><i class="fa fa-save"></i> Save</button>
					</td>
				</tfoot>
			</table>
		</form>
	</section>

</template>

<script type="text/javascript">
	import FormGroup from '../components/FormGroup'

	export default {
		name: "CompanySettings",
		props: [],
		components: {
			FormGroup
		},
		data () {
			return {
				settings: [],
				savedSettings: []
			}
		},
		computed: {
			renderedSettings() {
				let rendered = []

				this.settings.forEach(function(setting, index) {

					if (setting.type=="select")
						setting.options = JSON.parse(setting.options)

					rendered.push(setting)
				})

				return rendered
			}
		},
		methods: {
			fetchSettings() {
				let vm = this

				this.getJSON(window.apiBase + "setting/get").then(function(response){
					vm.settings = response
					vm.savedSettings = JSON.parse(JSON.stringify(response))

					vm.$emit("toggleSpinner",false)
				})
			},
			save() {
				let payload = {
					records: JSON.stringify(this.settings)
				}

				let vm = this

				this.postData(window.apiBase + "setting/save-batch",payload).then(function(response){
					vm.$emit("updateAlert",{
						visible: true,
						class: "alert-success",
						msg: "Settings saved"
					})

					vm.savedSettings = JSON.parse(JSON.stringify(vm.settings))
				})
			}
		},
		created() {
			this.$emit("toggleSpinner",true)
			this.fetchSettings();
		}
	}
</script>

<style type="text/css" scoped>

.form-group {
	margin-bottom: 0;
}

</style>

