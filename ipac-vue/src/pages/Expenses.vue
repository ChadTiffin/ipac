<template>
	<section>

		<div class="row">
			<div class="col-md-4">
				<div class="well">
					<h2>Filtering</h2>

					<form class="form-horizontal">

						<form-group label="From" col-class="col-md-4">
							<date-field v-model="filtering.dateFrom" v-on:change="filterExpenses"></date-field>
						</form-group>

						<form-group label="To" col-class="col-md-4">
							<date-field v-model="filtering.dateTo" v-on:change="filterExpenses"></date-field>
						</form-group>

						<form-group label="User" col-class="col-md-4">
							<select class="form-control" v-model="filtering.user" v-on:change="filterExpenses">
								<option :value="null">All Users</option>
								<option v-for="user in $root.users" :value="user.id">{{ user.first_name }} {{ user.last_name }}</option>
							</select>
						</form-group>

						<form-group label="Type" col-class="col-md-4">
							<select class="form-control" v-model="filtering.owner" v-on:change="setOwners">
								<option value="Client">Client</option>
								<option value="Project">Project</option>
							</select>
						</form-group>

						<form-group v-if="filtering.owner" :label="filtering.owner" col-class="col-md-4">
							<select class="form-control" v-model="filtering.owner_id" v-on:change="filterExpenses">
								<option :value="null">All {{ filtering.owner }}s</option>
								<option v-for="owner in owners" :value="owner.id">{{ filtering.owner == 'Client' ? owner.company : owner.project_name }}</option>
							</select>
						</form-group>

						<form-group v-if="filtering.owner_id && filtering.owner == 'Client'" v-on:change="filterExpenses" label="Location" col-class="col-md-4">
							<select class="form-control" v-model="filtering.location_id" v-on:change="filterExpenses">
								<option :value="null">All Locations</option>
								<option v-for="location in selectedClientLocations" :value="location.id">{{ location.location_name }}</option>
							</select>
						</form-group>

					</form>

					<div style="clear: both;"></div>
				</div>
			</div>
			<div class="col-md-8">
		    	<table class="table table-striped">
		    		<thead>
		    			<th>Date Uploaded</th>
		    			<th>User</th>
		    			<th>Client/Project</th>
		    			<th>Location</th>
		    			<th>Processed</th>
		    			<th>View</th>
		    			<th></th>
		    		</thead>
		    		<tbody>
		    			<tr v-for="expense in expensesWithTokens" :key="expense.id">
		    				<td>{{ formatDate(expense.submitted_at,'short') }}</td>
		    				<td>{{ expense.users.first_name }} {{ expense.users.last_name }}</td>
		    				<td>{{ expense.owner_type == "client" ? expense.clients.company : expense.projects.project_name }}</td>
		    				<td>{{ expense.locations && expense.owner_type == 'client' ? expense.locations.location_name : '' }}</td>
		    				<td>
		    					<input type="checkbox" v-model="expense.processed" :true-value="1" :false-value="0" style="height: 20px;width: 20px;" v-on:change="updateProcessed(expense)">
		    				</td>
		    				<td><a :href="expense.receipt_link" target="_blank">View Receipt</a></td>
		    				<td>
		    					<button class="btn btn-danger btn-sm" v-on:click="deleteExpense(expense)"><i class="fa fa-times"></i></button>
		    				</td>
		    			</tr>
		    			<tr v-if="expensesWithTokens.length == 0">
		    				<td colspan="7">
		    					No Expenses found
		    				</td>
		    			</tr>
		    		</tbody>
		    	</table>
		    </div>
		</div>

    	<modal-dialog
            v-if="confirmDialog.visible" 
            :title="confirmDialog.title" 
            :modal-visible="confirmDialog.visible" 
            :confirm-button-text="confirmDialog.buttonText"
            :button-class="confirmDialog.buttonClass"
            v-on:confirm="executeDeleteExpense(confirmDialog.parameters)"
            v-on:closeModal="confirmDialog.visible = false">
            {{ confirmDialog.message }}
        </modal-dialog>

	</section>

</template>

<script type="text/javascript">
	import ModalDialog from '../components/ModalDialog'
	import bus from '../bus.js'
	import ImageUploadField from '../components/ImageUploadField'
	import FormGroup from '../components/FormGroup'
	import TableList from '../components/TableList'
	import DateField from '../components/DateField'
	import vSelect from 'vue-select'

	export default {
		name: "Expenses",
		props: ["ownerType"],
		components: {
			ModalDialog,
			ImageUploadField,
			FormGroup,
			TableList,
			DateField,
			vSelect
		},
		data() {
			return {
				filtering: {
					dateFrom: new Date(new Date().getFullYear(),new Date().getMonth(),1),
					dateTo: new Date(),
					user: null,
					owner: "",
					owner_id: null,
					location_id: null
				},
				confirmDialog: {
                    visible: false,
                    title: "Delete Expense Receipt",
                    buttonText: "Delete",
                    buttonClass: "btn-danger",
                    parameters: "",
                    successFunction: "deleteExpense"
                },
                clients: [],
                projects: [],
                owners: [],
                filteredOwners: [],
                clientsProjects: [],
                expenses: [],
                expensesWithTokens: [],
                searchTerm: "",
			}
		},
		watch: {
			expenses() {
				let imageList = []

				let vm = this

				this.expenses.forEach(function(expense, index){
					imageList.push(expense.uploads.filename)
				})

				this.getJSON(window.apiBase+"image/get-image-tokens?images="+JSON.stringify(imageList)).then(function(response){

					let mergedExpenses = []
					vm.expenses.forEach(function(expense, index){
						Array.from(response).forEach(function(image, index) { 
							if (image.filename == expense.uploads.filename) {
								expense['receipt_link'] = window.apiBase + "image/serve/" + image.token + "/" + image.filename

								expense.updated_at = moment(expense.updated_at).format("YYYY-MM-DD")
							}
						})

						mergedExpenses.push(expense)
					})

					vm.expensesWithTokens = mergedExpenses;

				})
			}
		},
		computed: {
			selectedClientLocations() {
				let vm = this

				let client_locations = []

				this.clients.forEach(function(client, index) {
					if (client.id == vm.filtering.owner_id) {
						client_locations = client.locations
					}
				})

				return client_locations
			}
		},
		methods: {
			updateProcessed(expense) {
				let vm = this

				let payload = {
					id: expense.id,
					processed: expense.processed
				}

				this.postData(window.apiBase+"expense/save",payload).then(function(response){
					vm.$emit("updateAlert",{
						visible: true,
						class: "alert-success",
						msg: "Expense updated",
						icon: "fa-save"
					})
				});
			},
			setOwners() {

				if (this.filtering.owner == "Client")
					this.owners = this.clients
				else
					this.owners = this.projects

			},
			searchOwners(search,loading) {

				let vm = this

				let filter = ""
				let order = ""

				if (this.filtering.owner == "Client") {
					filter = JSON.stringify([
						["company",search,"and","like"]
					]);

					order = JSON.stringify(["company","ASC"]);
				}
				else {

					filter = JSON.stringify([
						["project_name",search,"and","like"]
					]);

					order = JSON.stringify(["project_name","ASC"]);
				}

				vm.getJSON(window.apiBase+this.filtering.owner+"/get?filters="+filter+"&order="+order).then(function(response){
					let owner_result = []

					Array.from(response).forEach(function(owner, index){
						
						if (vm.filtering.owner == "Project") {
							owner_result.push({
								name: owner.project_name,
								value: owner.id
							})
						}
						else {
							owner_result.push({
								name: owner.company,
								value: owner.id
							})
						}
					})

					vm.filteredOwners = owner_result
				})
			},
			deleteExpense(expense) {
				this.confirmDialog.visible = true;
				this.confirmDialog.message = "Are you sure you want to delete this expense?"
				this.confirmDialog.parameters = expense
			},
			executeDeleteExpense(expense) {
				let vm = this

				let payload = {
					id: expense.id
				}

				this.postData(this.apiBase+"expense/delete",payload).then(function(response){
					vm.filterExpenses()
					vm.confirmDialog.visible = false

					vm.$emit("updateAlert",{
						visible: true,
						class: "alert-danger",
						msg: "Expense deleted",
						icon: "fa-times"
					})
				})
			},
			filterExpenses() {
				let vm = this

				vm.$emit("toggleSpinner",true)

				let user_id = JSON.parse(localStorage.userDetails).id

				let filter = [
					["expenses.submitted_at >=",this.filtering.dateFrom],
					["expenses.submitted_at <=",this.filtering.dateTo],
				];

				if (this.filtering.user != null)
					filter.push(["expenses.user_id",this.filtering.user])

				if (this.filtering.owner != "")
					filter.push(["expenses.owner_type =",this.filtering.owner])

				if (this.filtering.owner_id != null) 
					filter.push(["expenses.owner_id =",this.filtering.owner_id])

				if (this.filtering.location_id != null) 
					filter.push(["expenses.location_id =",this.filtering.location_id])
				

				filter = JSON.stringify(filter)

				let order = JSON.stringify(["updated_at","DESC"]);

				this.getJSON(window.apiBase+"expense/get?filters="+filter+"&order="+order).then(function(response){
					vm.expenses = response

					vm.$emit("toggleSpinner",false)

				})
			}
		},
		created() {
			this.filterExpenses()
			this.fetchUsers()
			this.fetchClients()
			this.fetchProjects()
		}
	}
</script>

<style type="text/css" scoped>


</style>