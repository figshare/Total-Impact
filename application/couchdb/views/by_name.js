/*
 * lists docs by a key of [namespace, name] (each doc generally has several of these aliases)
 */
 function(doc) {
     if (doc.type == "item") {
         var key = [null, null];
         for (var namespace in doc.aliases) {
             key = [namespace, doc.aliases[namespace]];
             emit(key, doc.created_at);
         }
         emit (["totalimpact", doc._id], doc.created_at); // for the total-impact namespace
     }
}