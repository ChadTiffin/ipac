<template>
	<section class="small-section">
		<router-link to="/clients"><i class="fa fa-angle-double-left"></i> Back to Clients</router-link>

		<form class="form-horizontal" v-on:submit.prevent="save">
			<FormGroup label="Company" col-class="col-md-3">
				<input type="text" v-model='client.company' class="form-control">
			</FormGroup>

			<FormGroup label="Contact Name" col-class="col-md-3">
				<input type="text" v-model='client.contact_name' class="form-control">
			</FormGroup>

			<FormGroup label="Primary Phone" col-class="col-md-3">
				<input type="tel" v-model='client.primary_phone' class="form-control">
			</FormGroup>

			<FormGroup label="Email" col-class="col-md-3">
				<input type="text" v-model='client.email' class="form-control">
			</FormGroup>

			<FormGroup label="Address" col-class="col-md-3">
				<input type="text" v-model='client.address' class="form-control">
			</FormGroup>

			<FormGroup label="City" col-class="col-md-3">
				<input type="text" v-model='client.city' class="form-control">
			</FormGroup>

			<FormGroup label="Province" col-class="col-md-3">
				<input type="text" v-model='client.province' class="form-control">
			</FormGroup>

			<FormGroup label="Postal Code" col-class="col-md-3">
				<input type="text" v-model='client.postal_code' class="form-control">
			</FormGroup>

			<div class="alert" :class="alert.class" v-if="alert.visible">
				{{ clientDialog.alert.msg }}
				<p v-if="alert.hasErrors" v-for="error in alert.errors">
					{{error}}
				</p>
			</div>

			<button class="pull-right btn btn-success"><i class="fa fa-save"></i> Save</button>
		</form>
	</section>

</template>

<script type="text/javascript">
	import FormGroup from '../components/FormGroup'

	export default {
		name: "ClientEditor",
		props: ["clients"],
		components: {
			FormGroup
		},
		data () {
			return {
				alert: {
					visible : false,
					class: "",
					msg: "",
					hasErrors: false,
					errors: []
				},
				client: {}
			}
		},
		methods: {
			save() {
				let payload = this.client;
				let vm = this

				this.postData(window.apiBase+"client/save",payload).then(function(response) {
					vm.$emit("updateAlert",{
						visible: true,
						class: "alert-success",
						msg: "Client saved"
					})

					vm.$router.replace("/clients/edit/"+response.id)

					vm.$emit("clientListUpdate")
				})
			},
			fetchClient() {
				let vm = this
				let id = this.$route.params.id

				this.getJSON(window.apiBase + "client/find/"+id).then(function(response){
					vm.client = response
					vm.$emit("toggleSpinner",false)
				})
			}
		},
		created() {
			if (this.$route.params.id != "new")
				this.fetchClient()
			else
				this.$emit("toggleSpinner",false)
		}
	}
</script>

<style type="text/css" scoped>



</style>

