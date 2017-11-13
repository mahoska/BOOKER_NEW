<template>
  <div id="app">
    <img class="bg" src="static/fon2.jpg"/>
    <div id="header-booker">
      <nav class="navbar  navbar-fixed-top transp booker-nav" role="navigation">
        <div class="container-fluid">
          <router-link  :to="'/'"><span class="navbar-brand span-logo">BOARDROOM BOOKER</span></router-link>
          <ul class="nav navbar-nav nav-ul" v-if="isAuth==true">
            <li><router-link  :to="'/calendar'" class="nav-li">Calendar</router-link></li>
            <li v-if="isAdmin"><router-link  :to="'/employeeList'" class="nav-li">Employee List</router-link></li>
          </ul>
        </div>
      </nav>

      <!--is_login_check form-->
      <div  class="auth-form" v-if="isAuth==true">
        <table class="is_check">
          <tr>
            <td>
              <div class="log_span">Welcome, </div>
              <div class="login"> {{fullname}}</div>
            </td>
            <td class="small">
              <button class="btn btn-auth" @click="logout" >
                <span class="glyphicon glyphicon-log-out" aria-hidden="true" ></span>
              </button>
            </td>
          </tr>
          <tr>
            <td>Select <br> hours-format:</td>
            <td class="small">
              <select v-model="hourFormat" class="form-control hour-sel" >
                <option  value="h12">12</option>
                <option  value="h24">24</option>
              </select>
            </td>
          </tr>
        </table>
      </div>
      <!--is_login_check form-->
    </div>
    <div class="authorization">
      <div class="row" v-if="isAuth==false">
        <div  class="col-sm-4 col-md-4"></div>
        <div  class="col-sm-4 col-md-4">
          <div class="err">{{errLog}}</div>
          <div  class="form-group">
            <label for="login">Login</label>
            <input type="text" class="form-control" id="login"  placeholder="login" v-model.trim="login">
          </div>
          <div  class="form-group">
            <label for="password">Password</label>
              <input type="password" class="form-control" id="password" placeholder="password" v-model.trim="password" >
          </div>
          <div class="center">
            <button class="btn btn-auth" @click="flogin">Login</button> 
            <button class="btn btn-auth" @click="cancel" >Cancel</button>
          </div>
        </div>
        <div  class="col-sm-4 col-md-4"></div>  
      </div>
    </div>

    <div class="container main-container">
      <router-view :hourFormat="hourFormat"   @logout="logout"></router-view>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
export default {
  name: 'app',
   data () { 
    return{
      fullname: '',
      hourFormat: 'h24',
      isAuth: true,
      isAdmin: false,
      login: '',
      password: '',
      errLog: '',
    }
  },
  created: function(){
  
    var self = this;
    if(localStorage['hashLog']) {
      axios.get(serverUrl + 'BOOKER/client/api/user/'+localStorage['hashLog'], this.config)
        .then(function (response) {
          if(response.data.err && response.data.err == true) {
            self.logout(); 
          }else {
            var clientId = parseInt(response.data.data.id); 
            if(clientId > 0) 
            {
              self.isAuth = true;
              self.fullname = response.data.data.fullname;
              if(response.data.data.role == 'admin') {
                self.isAdmin = true;
              }
            }else {
              self.logout();
            }
          }
        })
        .catch(function (error) {
          self.logout();
          //console.log(error);
        });
    }else {
      this.logout();
    }
  },
  methods:{
    logout: function() {
      localStorage['hashLog'] = '';
      this.fullname = '';
      this.isAuth =  false;
      this.isAdmin = false;
      this.login = ''; 
      this.password = '';
      this.$router.push({path:'/'});
    },

    flogin: function() {
      if(this.login == '' || this.password == '') {
				this.errLog = 'not all fields are filled';
			}else {
        var self = this;
        this.errLog = '';

        axios.put(serverUrl + 'BOOKER/client/api/user/', 
        {
          login: this.login,
          password: this.password
        }, this.config)
        .then(function (response) {
          if(response.data.err && response.data.err == true) {
            self.errLog = response.data.data;
            setTimeout(function () {
                self.errLog = self.password = self.login = '';
            },2500);
          }else {
            self.isAuth = true;
            localStorage['hashLog'] = response.data.data.status;
            self.fullname = response.data.data.fullname;
            if(response.data.data.role == 'admin') {
              self.isAdmin = true;
            }
            self.$router.push({path: '/calendar'});
          }
        })
        .catch(function (error) {
            self.errLog = 'somthing wrong, authorization error';
            setTimeout(function () {
              self.errLog = '';
            },2500);
        });
      }
    },

    cancel: function(){
      this.login = ''; 
      this.password = '';
      this.errLog = '';
    }
  }
}
</script>

<style>
img.bg {
  min-height: 100%;
  min-width: 1024px;
  width: 100%;
  height: auto;
  position: fixed;
  z-index: 1;
  top: 0;
  left: 0;
}

@media screen and (max-width: 1024px) { 
  img.bg {
    left: 50%;
    margin-left: -512px; 
  }
}

.clearfix:after{
	content: ".";
	display: block;
	height: 0;
	clear: both;
	visibility: hidden;
}

#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color:#FFF;
}

.main-container{
  position: relative;
  z-index: 1000;
  margin-top: 130px;
}

.mynav-table{
  position: fixed;
  opacity: 1;
  top: 10px;
  right: 10px;
  z-index: 999;
  text-align: right;
}

.navbar {
  background-color:#2c3037;
  opacity: 0.7;
  z-index: 9;
  height: 80px;
  line-height: 80px;
}

.navbar ul li{
  height: 80px;
  width: 110px;
}

.nav-li{
  margin-top: 10px;
  font-weight: bold;
  text-align: center;
  font-size: 15px;
}

.nav-ul li:hover{
  background: #eee;
}

.span-logo{
  font-size: 40px;
  font-weight: bold;
  line-height: 80px;
  color: #3633B9;
  cursor:pointer;
  margin-top: -15px;
}

.auth-form{
  position: fixed;
  opacity: 1;
  top: 5px;
  right: 2px;
  z-index: 999;
  max-width: 300px;
  font-size: 12px;
}

.is_check td{
  line-height:17px;
  text-align: left;
  width: 230px;
}

.is_check{
  margin-top:5px;
  margin-left: 12px;
}

.small{
  width: 70px;
}

.login{
  text-align: center;
  font-size: 13px;
  color: lightblue;
  text-decoration: underline;
}

.hour-sel{
  font-size: 12px; 
  height: 20px; 
  padding:0; 
  width: 50px;
}

.authorization{
  position: relative;
  z-index: 1000;
  margin-top: 200px;
  text-align:left;
}
</style>
