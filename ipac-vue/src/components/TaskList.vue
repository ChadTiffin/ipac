<template>
	<div>
		<button v-if="includeNewButton && editable" class="btn btn-success pull-right" v-on:click="newTask"><i class="fa fa-plus"></i> Task</button>

		<h2>{{heading}}</h2>

		<div style="clear: both;"></div>

		<div v-for="task in tasks" class="panel panel-default task">
			<div class="panel-body">
				
				<div class="task-controls">
					<div v-if="editable">
						<button style="position: absolute;top: 5px;right: 5px;" class="btn btn-default btn-sm" title="Edit Task" v-on:click="editTask(task)">
							<i class="fa fa-pencil"></i>
						</button>
						<button class="btn btn-default btn-sm" v-on:click="toggleTaskCompletion(task)">
							<span v-if="!task.completed_at">
								<i class="fa fa-square-o"></i>
								Mark as Done
							</span>
							<span v-else>
								<i class="fa fa-check-square-o"></i>
								Mark as Incomplete
							</span>
						</button>
						<div class="checkbox" v-if="task.billable == 1">
							<label>
								<input type="checkbox" v-model="task.billed" v-on:change="toggleTaskBilled(task)" :true-value="1" :false-value="0"> Task is Billed
							</label>
						</div>
					</div>
					
				</div>

				<div class="task-description">
					<i v-if="task.priority == 2" class="fa fa-exclamation important-icon"></i>

					{{ task.description }}
					<div v-if="!task.is_complete" class="task-meta">
						<div v-if="task.due_date" class="due-date">Due {{ formatDate(task.due_date,'long') }}</div>
						<div class="assigned-to">Assigned to {{ task.assigned_to_user.first_name }} {{ task.assigned_to_user.last_name }}</div>
					</div>

					<div v-else class="task-meta">
						<div class="completed-date">Completed {{ formatDate(task.completed_at,'long, minutes') }}</div>
						<div class="assigned-to">Completed By {{ task.completed_by_user.first_name }} {{ task.completed_by_user.last_name }}</div>
					</div>
				</div>

			</div>
		</div>

		<modal-dialog
	        v-if="taskDialog.visible" 
	        title="Edit Task" 
	        :modal-visible="taskDialog.visible" 
	        confirm-button-text="Save"
	        button-class="btn-primary"
	        v-on:confirm="saveTask(taskDialog.values)"
	        v-on:closeModal="taskDialog.visible = false">

	        <form-group label="" col-class="col-md-0" style="margin-bottom: 0;">
	        	<textarea class="form-control" v-model="taskDialog.values.description" placeholder="Task Description" style="min-height: 100px;"></textarea>
	        </form-group>

	        <form-group label="Due Date" col-class="col-md-3">
	        	<date-field v-model="taskDialog.values.due_date"></date-field>
	        </form-group>

	        <form-group label="Priority" col-class="col-md-3">
	        	<select class="form-control" v-model="taskDialog.values.priority">
	        		<option value="0">Low</option>
	        		<option value="1">Normal</option>
	        		<option value="2">High</option>
	        	</select>
	        </form-group>

	        <form-group label="Assigned To" col-class="col-md-3">
	        	<select class="form-control" v-model="taskDialog.values.assigned_to">
	        		<option v-for="user in $root.users" :value="user.id">{{ user.first_name }} {{ user.last_name }}</option>
	        	</select>
	        </form-group>

	        <div class="checkbox" style="margin-bottom: 10px;">	        	
	        	<label>
	        		<input type="checkbox" v-model="taskDialog.values.billable" :true-value="1" :false-value="0">
	        		Task is Billable
	        	</label>
	        </div>

	        <div v-if="taskDialog.values.billable == 1" class="panel panel-default">

	        	<div class="panel-body">
		       		<form-group label="Price" col-class="col-md-1">

		       			<div style="width: 50%;display: inline-block;">
		       				<input type="number" step="0.01" class="form-control" title="Amount" v-model="taskDialog.values.price" placeholder="Price 0.00" >
		       				<div class="help">Price Before Tax</div>
		       			</div>

		       			<div style="width: 24%;display: inline-block;">
		       				<input type="number" step="0.01" class="form-control" title="Tax" v-model="taskDialog.values.tax" placeholder="Tax 0.00" >
		       				<div class="help">Tax</div>
		       			</div>

		       			<div style="width: 24%;display: inline-block;">
			       			<select class="form-control" v-model="taskDialog.values.currency" >
			       				<option>CAD</option>
			       				<option>USD</option>
			       			</select>
			       			<div class="help">Currency</div>
			       		</div>
		       		</form-group>

		       		<div class="checkbox" style="margin-bottom: 10px;">	        	
			        	<label>
			        		<input type="checkbox" v-model="taskDialog.values.billed" :true-value="1" :false-value="0">
			        		Task has been Billed
			        	</label>
			        </div>
			    </div>

	        </div>


	        <button slot="footer" class="btn btn-danger pull-left" v-on:click="deleteTask(taskDialog.values.id)"><i class="fa fa-times"></i> Delete Task</button>

	    </modal-dialog>
	</div>

</template>

<script type="text/javascript">
	import ModalDialog from '../components/ModalDialog'
	import FormGroup from '../components/FormGroup'
	import DateField from '../components/DateField'

	export default {
		name: "TaskList",
		props: ["tasks","filterIsComplete","filterBilled","heading","includeNewButton","editable"],
		components: {
			ModalDialog,
			FormGroup,
			DateField
		},
		data() {
			return {
				filterMatch: null,
				taskDialog: {
					visible: false,
					values: {} 
				}
			}
		},
		methods: {
			newTask() {
				this.taskDialog.values = {
					due_date: "",
					priority: 1,
					assigned_to: null,
					description: "",
					owner_type: "project",
					owner_id: this.$route.params.id,
					billable: 0,
					price: "",
					tax: "",
					currency: "CAD"
				}
				this.taskDialog.visible = true;
			},
			editTask(task) {
				
				
				this.taskDialog.values = {
					id: task.id,
					due_date: task.due_date,
					priority: task.priority,
					assigned_to: task.assigned_to,
					description: task.description,
					billable: task.billable,
					price: task.price,
					tax: task.tax,
					currency: task.currency
				}
				this.taskDialog.visible = true;
			},
			saveTask(payload) {
				let vm = this

				if (payload.due_date == "0000-00-00")
					payload.due_date = null

				this.postData(window.apiBase + "task/save",payload).then(function(response){
					vm.taskDialog.visible = false

					vm.$emit("modelChanged");
				})
			},
			deleteTask(task_id) {
				let vm = this

				let payload = {
					id: task_id
				}

				this.postData(window.apiBase+"task/delete",payload).then(function(response){
					vm.$emit("modelChanged");
					vm.taskDialog.visible = false

					vm.$emit("updateAlert",{
                        visible: true,
                        class: "alert-danger",
                        msg: "Task deleted"
                    })
				})
			},
			toggleTaskCompletion(task) {
				let payload = {}

				if (task.completed_at == null) {

					payload = {
						id: task.id,
						completed_at: "{{{server_now}}}",
						completed_by: "{{{current_user}}}"
					}
				}
				else {
					payload = {
						id: task.id,
						completed_at: null,
						completed_by: 0
					}
				}

				task.is_complete = 2

				let vm = this

				this.postData(window.apiBase+"task/save",payload).then(function(response) {
					
					vm.$emit("modelChanged");
				})
			},
			toggleTaskBilled(task) {
				let payload = {
					id: task.id,
					billed: task.billed
				}

				let vm = this

				this.postData(window.apiBase + "task/save",payload).then(function(){
					vm.$emit("modelChanged")
				})
			}
		},
		created() {
			if (this.showType == "complete")
				this.filterMatch = null

		}
	}
</script>

<style type="text/css" scoped>

.task.panel {
	margin-bottom: 5px;
	margin-top: 10px;
}

.task .panel-body {
	position: relative;
	padding: 7px;
	padding-bottom: 3px;
}

.task .edit-button {
	position: absolute;
	top: 2px;
	right: 2px;
	z-index: 2;
}
.task-controls {
	display: inline-block;
	width: 150px;
	vertical-align: top;
}

.task-description {
	display: inline-block;
	width: calc(100% - 185px);
	margin-top: 5px;
	margin-bottom: 5px;
}

.task-meta {
	font-size: 10pt;
	margin-top: 5px;
	color: #606060;
}

.task-meta div {
	display: inline-block;
	margin-right: 15px;
}

.task-meta .due-date {
	color: #d9534f;
	
}

.task-meta .completed-date {
	color: #31a01f;
}

.task-description .important-icon {
	position: absolute;
	top: 5px;
	right: 5px;
	color: #d9534f;
}

.form-group .help {
	text-align: right;
	padding-right: 10px;
	color: #808080;
}
</style>