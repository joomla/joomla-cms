export default function sortArray(array, by, direction) {
  return array.sort((a, b) => {
    // By name
    if (by === 'name') {
      if (direction === 'asc') {
        return a.name.toUpperCase().localeCompare(b.name.toUpperCase(), 'en', { sensitivity: 'base' });
      }
      return b.name.toUpperCase().localeCompare(a.name.toUpperCase(), 'en', { sensitivity: 'base' });
    }
    // By size
    if (by === 'size') {
      if (direction === 'asc') {
        return parseInt(a.size, 10) - parseInt(b.size, 10);
      }
      return parseInt(b.size, 10) - parseInt(a.size, 10);
    }
    // By dimension
    if (by === 'dimension') {
      if (direction === 'asc') {
        return (parseInt(a.width, 10) * parseInt(a.height, 10)) - (parseInt(b.width, 10) * parseInt(b.height, 10));
      }
      return (parseInt(b.width, 10) * parseInt(b.height, 10)) - (parseInt(a.width, 10) * parseInt(a.height, 10));
    }
    // By date created
    if (by === 'date_created') {
      if (direction === 'asc') {
        return new Date(a.create_date) - new Date(b.create_date);
      }
      return new Date(b.create_date) - new Date(a.create_date);
    }
    // By date modified
    if (by === 'date_modified') {
      if (direction === 'asc') {
        return new Date(a.modified_date) - new Date(b.modified_date);
      }
      return new Date(b.modified_date) - new Date(a.modified_date);
    }

    return array;
  });
}
