<template>
	<section>
		<table-list 
			:records="audits" 
			:fields="fields" 
			has-edit="/local-audits/{?}" 
			row-clickable="true">
		</table-list>
	</section>

</template>

<script type="text/javascript">
	import TableList from '../components/TableList'
	import ButtonGroup from '../components/ButtonGroup'
	import Spinner from '../components/Spinner'

	export default {
		name: "LocalAudits",
		components: {
			TableList,
			ButtonGroup,
			Spinner
		},
		data () {
			return {
				fields: [
					{
						key: "clientName",
						label: "Client"
					},
					{
						key: "auditLocation",
						label: "Location"
					},
					{
						key: "auditDate",
						label: "Audit Date"
					},
					{
						key: "versions",
						label: "# Versions Saved"
					}
				],
				audits: []
			}
		},
		computed: {
		},
		methods: {

		},
		created() {
			if ("localAuditBackups" in localStorage) {
				let audits = JSON.parse(localStorage.localAuditBackups)

				let mergedAudits = []

				audits.forEach(audit => {
					let addAudit = true

					let versionCount= 0

					mergedAudits.forEach(mergedAudit => {
						if (mergedAudit.auditData.id == audit.auditData.id) {
							addAudit = false
						}
					})

					//now search for matching audits so we can count them
					audits.forEach(nestedAudit => {
						if (nestedAudit.auditData.id == audit.auditData.id)
							versionCount++
					})

					if (addAudit) {
						audit.versions = versionCount
						audit.id = audit.auditData.id
						mergedAudits.push(audit)
					}

				})

				this.audits = mergedAudits
			}
			this.$emit("toggleSpinner",false)

		}
	}
</script>

<style type="text/css" scoped>



</style>

