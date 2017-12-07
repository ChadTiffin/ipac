<template>
	<section>
		<div class="button-bar">
			<router-link to="/clients/new" class="btn btn-success"><i class="fa fa-plus"></i> Client</router-link>
		</div>

		<div class="row">
			<div class="col-md-3 col-sm-4">
				<div class="well">
					<h3>Filter</h3>

					<h4>Current Phase</h4>

					<button-group
						v-if="clientPhaseFilteringButtons.length > 0"
						vertical="true" 
						:buttons="clientPhaseFilteringButtons" 
						:full-width="true"
						v-model="clientsFiltering.activePhase">
					</button-group>
					<spinner v-else :inline="true"></spinner>

				</div>
			</div>
			<div class="col-md-9 col-sm-8">
				<table-list 
					:records="filteredClients" 
					:fields="fields" 
					has-edit="/clients/{?}/locations" 
					row-clickable="true"
					v-on:modelChange="clientsChanged">
				</table-list>
			</div>

		</div>
	</section>

</template>

<script type="text/javascript">
	import TableList from '../components/TableList'
	import ButtonGroup from '../components/ButtonGroup'
	import Spinner from '../components/Spinner'

	export default {
		name: "Clients",
		props: ["clients"],
		components: {
			TableList,
			ButtonGroup,
			Spinner
		},
		data () {
			return {
				fields: [
					{
						key: "company",
						label: "Company"
					},
					{
						key: "contact_name",
						label: "Contact Name"
					},
					{
						key: "phone",
						label: "Phone"
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
						key: "activePhaseLabel",
						label: "Current Phase"
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
				clientsFiltering: {
					activePhase: "all"
				},
				clientPhaseFilteringButtons: []
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
		computed: {
			filteredClients() {
				let filteredClients = [];
				let vm = this

				this.clients.forEach(function(client, index){

					//find active phase
					let activePhase = null
					let activePhaseLabel = ""
					client.owner_phases.forEach(function(phase, index){
						if (phase.active == 1)  {
							activePhase = phase.phase_id
							activePhaseLabel = phase.phases.label
						}
					})

					if (vm.clientsFiltering.activePhase == "all" || vm.clientsFiltering.activePhase == activePhase) {
						//include this client and add active phase label
						client['activePhaseLabel'] = activePhaseLabel

						filteredClients.push(client)
					}
				})

				return filteredClients
			}
		},
		methods: {
			fetchPhases() {
				let vm = this

				let order = JSON.stringify(["priority","ASC"])

				this.getJSON(window.apiBase+"phase/get?order="+order).then(function(response){
					
					vm.clientPhaseFilteringButtons = [{text: "All", value: "all"}]
					response.forEach(function(phase, index) {
						vm.clientPhaseFilteringButtons.push({
							text: phase.label,
							value: phase.id
						})
					})

					vm.clientsFiltering.activePhase = "all"
				})
			},
			clientsChanged() {
				this.$emit("clientsChanged")
			}
		},
		created() {
			this.fetchPhases()

			this.$emit("toggleSpinner",false)
			this.$emit("clientsChanged")
		}
	}
</script>

<style type="text/css" scoped>



</style>

