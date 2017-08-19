export default {
	methods: {

		getJSON: function(url) {
			let vm = this
			return new Promise(function(resolve, reject) {

				fetch(url,{
					credentials: 'include',
					headers: new Headers({
						'Content-Type': 'text/plain',
						'x-api-key': localStorage.apiKey
					})
				})
					.then(function(response){
						return response.json();
					})
					.catch(function(e){
						console.log(e)
					})
					.then(function(j){

						if (typeof j == 'undefined') {//seem to be offline 

							let response = {
								status: "offline"
							}

							vm.$root.isOffline = true

							resolve(response)
						}
						else {
							if ("status" in j && j.status == "unauthorized")
								window.location = "/login"
								//console.log("redirect to login")

							else 
								resolve(j)
						}
					})
			});

		},
		postForm: function(form_id) {
			let vm = this
			return new Promise(function (resolve, reject) {
				
				let form_el = document.getElementById(form_id)

				let url = form_el.getAttribute("action")
				let payload = new FormData(form_el)

				if (typeof localStorage.apiKey != 'undefined')
					payload.append("key",localStorage.apiKey);

				fetch(url,{
					method: "POST",
					credentials: 'include',
					body: payload,
					/*headers: new Headers({
						'Content-Type': 'multipart/form-data',
						'x-api-key': localStorage.apiKey
					})*/
				})
				.then(function(response) {
					return response.json()
				})
				.catch(function(e){
					console.log(e)
				})
				.then(function(j) {
					if (typeof j == 'undefined') {//seem to be offline 
						let response = {
							status: "offline"
						}

						vm.$root.isOffline = true

						resolve(response)
					}
					else {
						if ("status" in j && j.status == "unauthorized")
							window.location = "/login"
							//console.log("redirect to login")

						else 
							resolve(j)
					}
				})

			})
		},
		postData: function(url, data) {
			let vm = this
			return new Promise(function (resolve, reject) {

				let payload = new FormData()
				for (var key in data) {
					if (typeof data[key] == "object")
						data[key] = JSON.stringify(data[key])

					payload.append(key, data[key])
				}

				if (typeof localStorage.apiKey != 'undefined')
					payload.append("key",localStorage.apiKey);

				fetch(url, {
					method: "POST",
					credentials: 'include',
					body: payload,
					/*headers: new Headers({
						'Content-Type': 'multipart/form-data',
						'x-api-key': localStorage.apiKey
					})*/
				})
				.then(function(response) {
					return response.json()
				})
				.catch(function(e){
					console.log(e)
				})
				.then(function(j) {
					if (typeof j == 'undefined') {//seem to be offline 
						let response = {
							status: "offline"
						}

						vm.$root.isOffline = true

						resolve(response)
					}
					else {
						if ("status" in j && j.status == "unauthorized")
							window.location = "/login"
							//console.log("redirect to login")

						else 
							resolve(j)
					}
				})

			})
		}
	}
}