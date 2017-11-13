<template>
  <div class="employeeList">
    <router-link class="btn btn-auth"  :to="'/employeeList/registration'">
         Add user
    </router-link>
    <div class="row">
      <div  class="col-sm-6 col-md-6">
        <div class="err">{{errDel}}</div>
        <div>{{resDel}}</div>
        <table class="table table-bordered">
          <tr>
              <th>User fullname</th>
              <th>Edit</th>
              <th>Delete</th>
          </tr>
          <tr v-for="user in users" :key="user.id">
            <td><a href="mailto:user.email">{{user.fullname}}</a></td>
            <td>
              <router-link class="btn btn-auth"  :to="'/employeeList/editUser/'+ user.id">
                <span class='glyphicon glyphicon-wrench' aria-hidden='true'></span>
              </router-link>
            </td>
            <td>
              <button class='btn btn-auth' @click="deleteUser(user.id)">
                <span class='glyphicon glyphicon-trash' aria-hidden='true'></span>
              </button>
            </td>
          </tr>
        </table>
      </div>
      <div  class="col-sm-6 col-md-6">
        <router-view @updateEmployeeList='getUsers'></router-view>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  name: 'employeeList',
  data () {
    return {
      users: [],
      user: {},
      errDel: '',
      resDel: '',
      userId: ''
    }
  },
  created() {
    var self = this;
    if(localStorage['hashLog']) {
    axios.get(serverUrl + 'BOOKER/client/api/user/' + localStorage['hashLog'], this.config)
      .then(function (response){
        var userId = parseInt(response.data.data.id); 
        if(userId > 0) {
          self.user = response.data.data;
          if(self.user.role != 'admin') {
            self.$router.push({path: '/calendar'})
          }
            self.userId = userId;
            self.getUsers();
        }else{
            self.$emit('logout')
        }
      }).catch(function (error) {
        self.$emit('logout')
      })
    }else{
      self.$emit('logout')
    }
  },
  methods: {

    /*
    function to obtain information about users
    */
    getUsers: function() {
      var self = this;
      axios.get(serverUrl + 'BOOKER/client/api/user/', this.config)
        .then(function (response) {
          self.users = response.data.data;
        })
        .catch(function (error) {
          //console.log(error);
        });
    },

    /*
    user delete function
    */
    deleteUser: function(userId) {
      var self = this;
      if(userId == this.userId) {
        this.errDel = 'you can not remove yourself';
        setTimeout(function () {
          self.errDel = '';
        },2500);
        return;
      }

      if (confirm('Are you sure you want to delete this user?')) {
        //is active and admin
        if(localStorage['hashLog']) {
          axios.get(serverUrl + 'BOOKER/client/api/user/' + localStorage['hashLog'], this.config)
            .then(function (response){
              if(parseInt(response.data.data.id) > 0) {
                if(response.data.data.role != 'admin') {
                  self.$router.push({path: '/calendar'})
                }
                else{
                //delete user
                axios.delete(serverUrl + 'BOOKER/client/api/user/' + userId, self.config)
                  .then(function (response) {
                    if(response.data.err && response.data.err == true){
                      self.err_del = response.data['data'];
                      setTimeout(function () {
                        self.errDel = '';
                      },2500);
                    }else {           
                      if(parseInt(response.data.data) == 1) {
                        self.resDel = 'Record  deleted';
                        setTimeout(function () {
                          self.resDel = '';
                        },2500); 
                        self.getUsers();
                        self.$router.push({path: '/employeeList'});
                      }else {
                        self.errDel = "Record doesn't deleted";
                        setTimeout(function () {
                            self.errDel = '';
                        },2500); 
                      }
                    }
                  })
                  .catch(function (error) {
                    self.errDel = "Record doesn't deleted";
                    setTimeout(function () {
                      self.errDel = '';
                    },2500); 
                  });
                }
            }else{
              self.$emit('logout');
            }
          }).catch(function (error) {
            self.$emit('logout');
          });
        }else{
          this.$emit('logout');
        }

       }
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.employeeList{
  margin-top: -70px;
}

table {
  font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;
  font-size: 14px;
  color: black;
  border-collapse: collapse;
  text-align: left;
  margin-top: 20px;
}

th {
  font-weight: normal;
  color: #66CABF;
  padding: 10px 12px;
}

td {
  background-image: url("static/back.png");
  filter: alpha(opacity=60);
  opacity: 0.6;
  color: #669;
  border-top: 1px solid white;
  padding: 10px 12px;
}

tr:hover td {
  background: transparent;
  color:#FFF;
}
</style>
