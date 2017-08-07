<template>
	<section>
		<button class="btn btn-success"><i class="fa fa-plus"></i> New Client</button>

		<table-list :records="clients" :fields="fields" has-edit="true" has-delete="true" v-on:edit="editClient"></table-list>

		<!--<ModalDialog
			v-show="clientDialog.visible" 
			:title="clientDialog.title" 
			:modal-visible="clientDialog.visible" 
			confirm-button-text="Save"
			v-on:closeModal="clientDialog.visible = false"
			v-on:confirm="submitClient">
			
			<FormGroup label="Company" col-class="col-md-3">
				<input type="text" v-model='clientDialog.fields.company' class="form-control">
			</FormGroup>

			<FormGroup label="Contact Name" col-class="col-md-3">
				<input type="text" v-model='clientDialog.fields.contact_name' class="form-control">
			</FormGroup>

			<FormGroup label="Primary Phone" col-class="col-md-3">
				<input type="text" v-model='clientDialog.fields.primary_phone' class="form-control">
			</FormGroup>

			<FormGroup label="Email" col-class="col-md-3">
				<input type="text" v-model='clientDialog.fields.email' class="form-control">
			</FormGroup>

			<FormGroup label="Company" col-class="col-md-3">
				<input type="text" v-model='clientDialog.fields.address' class="form-control">
			</FormGroup>

			<div class="alert" :class="clientDialog.alert.class" v-if="clientDialog.alert.visible">
				{{ clientDialog.alert.msg }}
				<p v-if="clientDialog.alert.hasErrors" v-for="error in clientDialog.alert.errors">
					{{error}}
				</p>
			</div>

		</ModalDialog>-->

		<!--<ModalDialog
			v-if="confirmDialog.visible" 
			:title="confirmDialog.title" 
			:modal-visible="confirmDialog.visible" 
			:confirm-button-text="confirmDialog.buttonText"
			v-on:confirm="confirmationDialogResponse(true)"
			v-on:closeModal="confirmationDialogResponse(false)">
			{{ confirmDialog.message }}
		</ModalDialog>-->
	</section>

</template>

<script type="text/javascript">
	import TableList from '../components/TableList'

	export default {
		name: "Clients",
		props: ["clients"],
		components: {
			TableList
		},
		data () {
			return {
				fields: [
					{
						key: "company",
						label: "Company Name"
					},
					{
						key: "contact_name",
						label: "Contact Name"
					},
					{
						key: "phone",
						label: "Company Name"
					},
					{
						key: "email",
						label: "Email"
					},
					{
						key: "address",
						label: "Address"
					},
					{
						key: "city",
						label: "City"
					},
					{
						key: "province",
						label: "Province"
					},
					{
						key: "postal_code",
						label: "Postal Code"
					}
				],
				clientDialog: {
					visible: false,
					title: "",
					fields: {},
					alert: {
						visible: false,
						hasErrors: false,
						errors: [],
						msg: "",
						class: ""
					}
				}
			}
		},
		methods: {
			editClient() {

			},
		},
		created() {
			this.$emit("toggleSpinner",false)
		}
	}
</script>

<style type="text/css" scoped>



</style>

