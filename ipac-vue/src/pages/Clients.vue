<template>
	<section>
		<div class="button-bar">
			<router-link to="/clients/new" class="btn btn-success"><i class="fa fa-plus"></i> Client</router-link>
		</div>

		<table-list 
			:records="clients" 
			:fields="fields" 
			has-edit="/clients/" 
			row-clickable="true"
			v-on:modelChange="clientsChanged">
		</table-list>

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
				confirmDialog: {
					visible: false,
					title: "",
					buttonText: "Delete",
					buttonClass: "btn-danger",
					parameters: "",
					successFunction: ""
				},
				/*otherButtons: [
					{
						link: "/reports/client/",
						text: " Reports",
						icon: "fa-wpforms",
						class: "btn-primary"
					},
					{
						link: "/reports/client/",
						text: " Audits",
						icon: "fa-balance-scale",
						class: "btn-primary"
					}
				]*/
			}
		},
		methods: {
			clientsChanged() {
				this.$emit("clientsChanged")
			}
		},
		created() {
			this.$emit("toggleSpinner",false)
			this.$emit("clientsChanged")
		}
	}
</script>

<style type="text/css" scoped>



</style>

