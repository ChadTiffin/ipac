<template>
	<section>
		<div class="button-bar">
			<router-link to="/local-audits" class='router-link'><i class="fa fa-angle-double-left"></i> Back to Audits on Device</router-link>
		</div>

		<div class="row">
			<div class="col-md-3 col-sm-4">
				<ul class="list-group">
					<li 
						v-for="(audit,index) in auditVersions" 
						class="list-group-item"
						v-on:click="showAuditVersion(index)">
						Version #{{ index+1 }}
					</li>
				</ul>
			</div>
			<div class="col-md-9 col-sm-8">

				<h3>Raw Audit Data (Version #{{activeAuditNum}})</h3>
				<p>Provide this data to the developer to recover into a useful audit</p>
				<textarea class="form-control">{{ activeAudit }}</textarea>

				<article v-if="activeAuditObj">
					<h2>Audit</h2>

					<div class="section" v-for="section in activeAuditObj.auditData.form_values">
						<h3>{{ section.heading }}</h3>

						<table v-if="'fields' in section" class="table table-sm">
							<tbody>
								<tr v-for="field in section.fields">
									<th>{{ field.question }}</th>
									<td>{{ field.value }}</td>
								</tr>
							</tbody>
						</table>

						<div v-if="'subSections' in section" v-for="subSection in section.subSections" class="subsection">
							<h4>{{ subSection.heading }}</h4>

							<table v-if="'fields' in subSection" class="table table-sm">
								<tbody>
									<tr v-for="field in subSection.fields">
										<th>{{ field.question }}</th>
										<td>{{ field.value }}</td>
									</tr>
								</tbody>
							</table>
							<hr>
						</div>

					</div>
				</article>

			</div>

		</div>
	</section>

</template>

<script type="text/javascript">
	import TableList from '../components/TableList'
	import ButtonGroup from '../components/ButtonGroup'
	import Spinner from '../components/Spinner'

	export default {
		name: "LocalAudit",
		components: {
			TableList,
			Spinner
		},
		data () {
			return {
				auditVersions: [],
				activeAudit: "",
				activeAuditObj: false,
				activeAuditNum: null
			}
		},
		computed: {

		},
		methods: {
			showAuditVersion(index) {

				this.activeAudit = JSON.stringify(this.auditVersions[index].auditData)
				this.activeAuditNum = index+1
				this.activeAuditObj = this.auditVersions[index]

				console.log(this.activeAuditObj)
			}
		},
		created() {

			let vm = this

			this.auditVersions = JSON.parse(localStorage.localAuditBackups).filter(audit => {
				if (audit.auditData.id == vm.$route.params.id)
					return true
			})

			this.$emit("toggleSpinner",false)
		}
	}
</script>

<style type="text/css" scoped>

	textarea {
		height: 100px;
	}

	.list-group-item:hover {
		background-color: #e0e0e0;
		cursor: pointer;
	}

	.section {
		margin-bottom: 10px;
		padding: 10px;
		background-color: #f7f7f7;
	}

</style>

