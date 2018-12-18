////////////////////

var dataform = {
  input: {selected:{desc:"Check any skill"},
          rating:{desc:"Self Rating 1-10",type:'int', size:'small'},
          desc:{desc:"Highligh your experience",type:"text", size:"medium" },
  },
  data: ['path','rating','desc'],
  };
//Tree Builder 
var nodes = [
  {path:[0], label:"Technical Development",},
  {path:[0,0], label:"Web",},
  {path:[0,0,0], label:"Python",},
  {path:[0,0,1], label:"JavaScript",},
  {path:[0,0,2], label:"PHP",},
  {path:[0,0,3], label:"Ruby",},
  {path:[0,0,4], label:"C/C++",},
  {path:[0,1], label:"Mobile",},
  {path:[0,1,0], label:"iOS",},
  {path:[0,1,1], label:"Android",},
  {path:[0,1,1], label:"Multiplatform",},
  {path:[0,2], label:"Application",},
  {path:[0,3], label:"Engineering",},
  {path:[0,3,0], label:"Elictrical Engineering",},
  {path:[0,3,1], label:"Mechanical Engineering",},


  {path:[1], label:"Marketing",},
  {path:[1,0], label:"Social Media Marketing",},
  {path:[1,1], label:"Traditinal Corporate Marketing",},

  {path:[2], label:"Business",},
  {path:[2,0], label:"Business Development",},
  {path:[2,1], label:"Business Strategy",},
  {path:[3], label:"Design",},
  {path:[3,0], label:"UX Design",},
  {path:[3,1], label:"Graphic Design",},
  {path:[3,2], label:"Art Direction",},
  {path:[3,3], label:"Engineering Graphics",},
  {path:[2,3], label:"Business Networking",},
  {path:[4,0], label:"Financial Planning & Management",},
  {path:[4,1], label:"Procuring Funding",},
  {path:[5], label:"Customer Relationship",},
  {path:[4], label:"Finance",},
  {path:[6], label:"Technical Magagement",},
  {path:[7], label:"Sales",},
];
var settings = {
  //Just filter by lenth, then order.
  params:{idx:null, ln:1},
  reset: function(xparams) {
    this.params = xparams;
  },
  showparams: function() {
    //console.log("These params: ",this.params);
  },
  filter: function(el) {
    return (el.path.length <= this.params.ln);
  }
}


class TreeNode {
  constructor(init) {
    this.path = init.path;
    this.label = init.label;
    this.tooltip = init.tooltip;
    this.children = {};
    this.parent = null;
    this.next = null;
    this.prev = null;
  }
  depth() {
    return this.path.length -1; //Zero based
  }
  segment(n) {
    if (this.depth()<n) {
      return null;
    } else {
      return this.path[n];
    }
  };
}

class TreeNodes  {
  // Could be data, or an arry of treeNode instances
   constructor(treeNodes) {
     this.treeNodes = {};
     this.root = {children:{}};
     treeNodes.sort(this.orderbypath);
     //var tmparr = [];
     treeNodes.forEach(el => {
       var tn = new TreeNode(el);
       this.place(tn);
     });
     //console.log("This treenodes:", this.treeNodes);
     //Now traverse & build
   }
  place(tn) {
     var depth = tn.depth();
     var parent = this.root;
     for (var i = 0 ; i <= depth ; i++) {
       var s = tn.segment(i);
       var last = (i === depth);
       if (last) {
         tn.parent = parent;
         if (parent.children[s]) {
           if (parent.children[s].placeholder) {
             tn.children = parent.children[s].children;
           } 
         } else {
           throw "Duplicate entry for this treeNode;";
         }
         parent.children[s] = tn;
         return;
     } else { //Traverse down
         if (parent.children[s]) {
           parent = parent.children[s];
         } else { // make a placeholder object
          var placeholder = {
            placeholder: true,
            parent: parent,
            children: {[s]:tn, }
          };
          parent.children[s] = placeholder;
        }
      }
    }

    throw "Didn't find a place for this tree node";
   }


   orderbypath(a,b) {
      var len = Math.min(a.path.length, b.path.length);
      for (var i = 0 ; i < len ; i++) {
       if (a.path[i] < b.path[i]) {
          return +1;;
        } else if  (a.path[i] > b.path[i]) {
          return -1;
        }
      }
      return a.path.length - b.path.length;
    }
}

var treeNodes = new TreeNodes(nodes);


