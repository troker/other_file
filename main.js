'use strict';

class User {
	static canC() {
		console.log('yeatt');
	}

	constructor(name, can) {
		this.name = name;
		this.can = can;
	}

  	sayHi() {
    	console.log(this.name);
  	}

  	tryItem() {
  		console.log(this.can);
  	}
}

class NewUser extends User {
	constructor(name, can, grip, tock) {
		super(name, can);
		this.grip = grip;
		this.tock = tock;
	}
}

let user = new User('tr1', 1);
let newUser = new NewUser('tr2', 2, 'true', 365);

user.sayHi();
user.tryItem();
console.log('-----------------------');
newUser.sayHi();
newUser.tryItem();
console.log(newUser.grip)
console.log(newUser.tock)

