<template>
	<table class="table table-striped">
        <thead>
            <tr>
                <th v-for="field in fields">{{field.label}}</th>
                <th v-if="hasEdit || hasDelete"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="record in records">
                <td v-for="field in fields">{{ record[field.key] }}</td>
                <td v-if="hasEdit || hasDelete">

                    <button v-if="hasEdit == 'true'" class="btn btn-default btn-sm" v-on:click="editRecord(record)">
                        <i class="fa fa-pencil"></i>
                    </button>

                    <router-link v-else :to="hasEdit + record.id" class="btn btn-default btn-sm">
                        <i class="fa fa-pencil"></i>
                    </router-link>

                    <button v-if="hasDelete" class="btn btn-danger btn-sm" v-on:click="deleteRecord(record)"><i class="fa fa-remove"></i></button>

                    <div class="other-buttons" v-for="button in otherButtons">
                        <router-link class="btn btn-sm" :to="button.link + record.id" :class="button.class">
                            <i v-if="button.icon" class="fa" :class="button.icon"></i>
                            {{ button.text }}
                        </router-link>
                    </div>
                </td>
            </tr>
            <tr v-if="records.length == 0">
                <td :colspan="fields.length">No records</td>
                <td v-if="hasEdit || hasDelete"></td>
            </tr>
        </tbody>
    </table>
</template>

<script type="text/javascript">

	export default {
		name: "TableList",
		props: [
            "records",
            "fields",
            "hasEdit", //if true, its a button that resolves to editRecord method on click, otherwise its a link that contains the root path "hasEdit/{record_id}" (slash is included in hasEdit)
            "hasDelete",
            "otherButtons"
        ],
		methods: {
            editRecord(record) {
                this.$emit("edit",record)
            },
            deleteRecord(record) {
                this.$emit("delete",record)
            }
        }
	}
</script>

<style type="text/css" scoped>
    .other-buttons {
        display: inline-block;
    }
</style>