'use strict';

/**
 * Performance handler
 * The point of this is simply to get how long the test took to run, its not to
 * test if the function works.  Im using Qunits time tracker in a hacky way
 *
 * @param  {function} func     function to test
 * @param  {array} args      array of arguments
 * @param  {number} iterations Number of times to execute the function
 * @return {true}            returns true
 */

var myPerf = function(iterations){
    return function(func, args){
        var i
        for(i = 0; i < iterations; i++){
            func.call(args);
        }
        return true;
    }
}

// this is my alteration so I can run both MIN / FULL tests
var runTests = function(title){

    QUnit.test(title + 'ID test', function(assert){
        assert.equal($id(), false, 'Empty Object');
        assert.equal($id(123), false, 'Numeric');
        assert.equal($id(null), false, 'Null');
        assert.equal($id(undefined), false, 'Undefined');
        assert.equal(typeof $id('age'), 'object', 'Object Check');
        assert.equal($id('age').value, 100, 'Object Value');
    });


    QUnit.test(title + 'Tag test', function(assert){
        assert.equal($tag(), false, 'Empty Object');
        assert.equal($tag(123).length, 0, 'Numeric');
        assert.equal($tag(null), false, 'Null');
        assert.equal($tag(undefined), false, 'Undefined');
        assert.equal(typeof $tag('span'), 'object', 'Object Check');
        assert.equal($tag('input').length, 5, 'Object Count');
    });

    QUnit.test(title + 'Class test', function(assert){
        assert.equal($class(), false, 'Empty Object');
        assert.equal($class(123).length, 0, 'Numeric');
        assert.equal($class(null), false, 'Null');
        assert.equal($class(undefined), false, 'Undefined');
        assert.equal(typeof $class('span'), 'object', 'Object Check');
        assert.equal($class('input').length, 5, 'Object Count');
    });


    // QUnit.test(title + 'PERFORMANCE - 1', function(assert){

    //     // set iteration in one place
    //     var p = myPerf(100000)

    //     // these will always return OK
    //     assert.equal(p(sortlist.trim1, '   trim   '), true);
    //     // assert.equal(p(sortlist.trim2, '   trim   '), true);
    // });

    // QUnit.test(title + 'PERFORMANCE - 2', function(assert){

    //     // set iteration in one place
    //     var p = myPerf(100000)

    //     // these will always return OK
    //     // assert.equal(p(sortlist.trim1, '   trim   '), true);
    //     assert.equal(p(dan.trim2, '   trim   '), true);
    // });

};

runTests('');
