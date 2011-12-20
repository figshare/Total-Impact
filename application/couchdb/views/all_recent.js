/*
 * Displays collections 
 * Returns a list of identifier for each artifact in the collection
 */
 function(doc) {
  if (doc.created_at > "1312268704") {
    emit(null, {id: doc._id, title: doc.title, created_at: doc.created_at});
  }
}
