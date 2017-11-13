<template>
  <div class="edit-user">
    <div class="row">
      <div  class="col-sm-1 col-md-1"></div>
      <div  class="col-sm-10 col-md-10">
        <div class="err" style="width: 100%">{{errUpd}}</div>
        <div  class="err success">{{isUpd}}</div>
        <div class="lbl_upd">User(id:{{userId}}) edit form:</div>
        <div  class="form-group" v-if="userShow">
          <label for="fullname">Fullname</label>
          <input type="text" class="form-control" id="fullname"  placeholder="fullname" v-model.trim="user.fullname"  @blur="checkName">
          <div class="err_small" v-if="errName">fullname contains invalid characters</div>
        </div>
        <div  class="form-group">
          <label for="email">Email</label>
          <input type="email" class="form-control" id="email"  placeholder="email" v-model.trim="user.email" @blur="checkEmail">
          <div class="err_small" v-if="errEmail">wrong email format</div>
        </div>
        <div  class="form-group">
          <label for="login">Login</label>
          <input type="text" class="form-control" id="login"  placeholder="login" v-model.trim="user.login" @blur="checkLogin">
          <div class="err_small">{{errLog}}</div>
        </div>
        <div  class="form-group">
          <label for="password">Password</label>
          <input type="password" class="form-control" id="password" placeholder="password" v-model.trim="user.password" >
        </div>
        <div class="form-group">
          <label>Role</label>
          <select v-model="user.role_id" class="form-control">
            <option v-for="role in roles" :value="role.id" :key="role.id">
              {{role.name}}
            </option>
          </select>
        </div>
        <div class="center">
          <button class="btn btn-auth" @click="editUser">Update user</button> 
          <button class="btn btn-auth" @click="cancel" type="cancel">cancel</button>
        </div>
      </div>
      <div  class="col-sm-1 col-md-1"></div>  
    </div>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  name: 'registration',
  props: ['hour_format'],
  data () {
    return {
      user: {},
      roles: [],
      isUpd: '',
      errUpd: '',
      errEmail: false,
      errLog: '',
      errName : false,
      userShow: true
    }
  },
  created() {
    var self = this;
    //check is user active or timeout session work and check is admin?
    if(localStorage['hashLog']) {
      axios.get(serverUrl + 'BOOKER/client/api/user/' + localStorage['hashLog'], this.config)
        .then(function (response) {
          var userId = parseInt(response.data.data.id); 
          if(userId > 0) {
            if(response.data.data.role == 'admin') {
              self.getRoles();
            }else {   
               self.$router.push({path: '/calendar'});
            }
          }else {   
              self.$emit('logout');
          }
      }).catch(function (error) {
        self.$emit('logout');
      });
    }else {
       this.$emit('logout');
    }
      this.getRoles();
  },

  methods:{

    /*
      user editing function
     */
    editUser: function() {
      this.isUpd = '';
      if(this.user.login == '' ||  this.user.fullname == '' ||
         this.user.email == '' || this.user.role_id == '') 
      {
        this.errUpd = 'not all fields are filled';
			}else {
        if (this.checkEmail() && this.checkName() && this.checkLogin()) {
          this.errUpd = '';    
          var self = this;

          if(localStorage['hashLog']) {
          axios.get(serverUrl + 'BOOKER/client/api/user/' + localStorage['hashLog'], this.config)
            .then(function (response){
              if(parseInt(response.data.data.id) > 0) {
                if(response.data.data.role != 'admin') {
                  self.$router.push({path: '/calendar'})
                }else {
                  //update user  
                  axios.put(serverUrl + 'BOOKER/client/api/user/', 
                  {
                    edit: true,
                    fullname: self.user.fullname,
                    login: self.user.login,
                    password: self.user.password,
                    email: self.user.email,
                    role_id: self.user.role_id,
                    id: self.userId
                  }, self.config)
                .then(function (response) {
                  if(response.data.err && response.data.err == true) {
                    self.errUpd = response.data['data'];
                    setTimeout(function () {
                      self.errUpd = '';
                    },3000);
                  }else {  
                  if(response.data.data == '1'){
                      self.isUpd = 'record was updeted';
                      setTimeout(function () {
                        self.isUpd = '';
                      },2500);
                      self.$emit('updateEmployeeList');
                  }
                  }
                })
                .catch(function (error) {
                    self.isUpd = 'No update was made. No new data was provided for the changes';
                    setTimeout(function () {
                      self.isUpd = '';
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
          
          setTimeout(function () {
            this.isUpd  = this.errUpd = '';
          },2500);  
        }
      }
  },
   
  /*
    deactivate and reset function
  */
  cancel: function() {
    this.errReg = this.isReg = this.errLog = this.errName = this.errEmail = '';
    this.$router.push({path: '/employeeList'});
  },

  /*
    validation email
    @return <Bool> flag - 
      return true - if email is valid and false otherwise
  */
  checkEmail: function() {
    var re = /^[\.\-_A-Za-z0-9]+?@[\.\-A-Za-z0-9]+?(\.)[A-Za-z0-9]{2,3}$/;
    if (re.test(this.user.email) != true) {
      this.errEmail = true;
      return false;
    }else {
      this.errEmail = false;
      return true;
    }
  },

  /*
    validation login
    @return <Bool> flag - 
      return true - if login is valid and false otherwise
  */
  checkLogin: function() {
    var re = /^[\-_A-Za-z0-9]+$/;
    if (re.test(this.user.login) !== true) {
      this.errLog = 'login contains invalid characters';
      return false;
    }else if (this.user.login.length < 6) {
      this.errLog ='login is too short';
      return false;
    }else {
      this.errLog = '';
      return true;
    }     
  },

  /*
    validation fullname
    @return <Bool> flag - 
      return true - if fullname is valid and false otherwise
  */
  checkName: function() {
    var re = /^[A-Za-z ]+$/;
    if (re.test(this.user.fullname) !== true) {
      this.errName = true;
      return false;
    }else {
      this.errName = false;
      return true;
    }
  },

  /*
    function to retrieve user role data
  */
  getRoles: function() {
    var self = this;
    axios.get(serverUrl+'BOOKER/client/api/role/', this.config)
      .then(function (response) {
        self.roles = response.data.data;
    })
    .catch(function (error) {
      //console.log(error);
    });
  },

  }, 
computed:{
  /*
  function to initialize the id of the user being edited and
   get information about it by id
  */
  userId: function() {
    this.userShow = true;
    var id = this.$route.params.userId;
    var self = this;
    axios.get(serverUrl + 'BOOKER/client/api/user/'+ id, this.config)
      .then(function (response) {
        self.user = response.data.data;
        self.user.password = '';
        if(self.user.length == 0) {
          self.errUpd = 'Information about this client is absent';
          self.userShow = false;
        }
      })
      .catch(function (error) {
        self.errUpd = 'Information about this client is absent';
        self.userShow = false;
      }); 
    return id;
  }
}

}
</script>
<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.edit-user{
  margin-top: -50px;
}

.center{
  text-align: center;
}

.lbl_upd{
  text-align: right;
  font-weight: bold;
  color:#3231b9;
  text-decoration: underline;
}
</style>
