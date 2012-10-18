
function Person(name) {
    this.name = name;
}

Person.prototype.greet = function(person) {
    return "Hello " + person.name + "!";
}

var john = new Person("John");
var jill = new Person("Jill");

console.log(john.greet(jill));
console.log(jill.greet(john));

