<template>
  <div class="add-client">
    <div class="row">
      <div  class="col-sm-1 col-md-1"></div>
      <div  class="col-sm-10 col-md-10">
        <div class="err" style="width: 100%">{{errReg}}</div>
        <div  class="success err">{{isReg}}</div>
        <div  class="form-group">
          <label for="fullname">Fullname</label>
          <input type="text" class="form-control" id="fullname"  placeholder="fullname" v-model.trim="fullname"  @blur="checkName">
          <div class="err_small" v-if="errName">fullname contains invalid characters</div>
        </div>
        <div  class="form-group">
          <label for="email">Email</label>
          <input type="email" class="form-control" id="email"  placeholder="email" v-model.trim="email" @blur="checkEmail">
          <div class="err_small" v-if="errEmail">wrong email format</div>
        </div>
        <div  class="form-group">
          <label for="login">Login</label>
          <input type="text" class="form-control" id="login"  placeholder="login" v-model.trim="login" @blur="checkLogin">
          <div class="err_small">{{errLog}}</div>
        </div>
        <div  class="form-group">
          <label for="password">Password</label>
          <input type="password" class="form-control" id="password" placeholder="password" v-model.trim="password" >
        </div>
        <div class="form-group">
          <label>Role</label>
          <select v-model="roleId" class="form-control">
            <option v-for="role in roles" :value="role.id" :key="role.id">
              {{role.name}}
            </option>
          </select>
        </div>
        <div class="center">
          <button class="btn btn-auth" @click="addUser">Registration</button> 
          <button class="btn btn-auth" @click="cancel" type="cancel">Cancel</button>
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
  props: ['hourFormat'],
  data () {
    return {
      fullname: '',
      login: '',
      password: '',
      email: '',
      roleId: 2,
      roles: [],
      isReg: '',
      errReg: '',
      errEmail: false,
      errLog: '',
      errName : false,
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

  methods: {
    /*
      deactivate and reset function
    */
    cancel() {
      this.login = this.password = this.fullname = this.email = this.role_id = '';
      this.errReg = this.isReg = this.errLog = this.errName = this.errEmail = '';
      this.$router.push({path: '/employeeList'});
    },

    /*
    validation email
    @return <Bool> flag - 
      return true - if email is valid and false otherwise
    */
    checkEmail() {
      var re = /^[\.\-_A-Za-z0-9]+?@[\.\-A-Za-z0-9]+?(\.)[A-Za-z0-9]{2,3}$/;
      if (re.test(this.email) != true) {
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
    checkLogin() {
      var re = /^[\-_A-Za-z0-9]+$/;
      if (re.test(this.login) !== true) {
        this.errLog = 'login contains invalid characters';
        return false;
      }else if (this.login.length < 6){
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
    checkName(){
      var re = /^[A-Za-z ]+$/;
      if (re.test(this.fullname) !== true) {
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
    getRoles(){
      var self = this;
      axios.get(serverUrl + 'BOOKER/client/api/role/', this.config)
        .then(function (response) {
          self.roles = response.data.data;
      })
      .catch(function (error) {
        //console.log(error);
      });
    },

    /*
    function of adding users
    */
    addUser() {
      this.isReg = '';
			if(this.login == '' || this.password == '' || this.fullname == '' || this.email == '' || this.roleId == ''){
        this.errReg = 'not all fields are filled';
			}else {
        if (this.checkEmail() && this.checkName() && this.checkLogin()) {
          this.errReg = '';   
          var self = this;
          var data = new FormData();
          data.append("login", this.login);
          data.append("password",this.password);
          data.append("fullname",this.fullname);
          data.append("email",this.email);
          data.append("role_id",this.roleId);  
          
          //check is user active or timeout session work
          var self = this;
          if(localStorage['hashLog']) {
            axios.get(serverUrl + 'BOOKER/client/api/user/' + localStorage['hashLog'], this.config)
              .then(function (response) {
                var userId = parseInt(response.data.data.id);
                if(userId > 0) {
                  axios.post(serverUrl + 'BOOKER/client/api/user/', data, self.config)
                  .then(function (response) {
                    if(response.data.err && response.data.err == true) {
                      self.errReg = response.data['data'];
                      setTimeout(function () {
                        self.errReg = ""
                      },2500);
                    }else {
                      if(parseInt(response.data.data) > 0) {
                        self.login = self.password = self.fullname  = self.email = '';
                        self.roleId = 2;
                        self.isReg = 'user is successfully added';
                        self.$emit('updateEmployeeList');
                        setTimeout(function () {
                          self.isReg = '';
                        },2500);
                      }
                      else {
                        self.errReg = 'sorry, there was a server error.Try again later';
                        setTimeout(function () {
                          self.errReg = '';
                        },2500);
                      }
                    }
                  })
                  .catch(function (error) {
                      self.errReg = 'sorry, there was a server error.Try again later';
                  });
                }else {
                  self.$emit('logout');
                }
            }).catch(function (error) {
              self.$emit('logout');
            });
          }else {
            this.$emit('logout');
          }
        }
      }
      setTimeout(function () {
        this.errReg = '';
        this.isReg = '';
      },2500);  
    }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
.add-client{
  text-align:left;
  color: #FFF;
  margin-top: -40px;
}

.center{
  text-align: center;
}
</style>
