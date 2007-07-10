<?php
/* This class is part of the XP framework
 *
 * $Id$ 
 */
  uses(
    'rdbms.criterion.CountProjection',
    'rdbms.criterion.ProjectionList',
    'rdbms.criterion.SimpleProjection'
  );

  /**
   * This is the projection factory.
   * With the projection API a result set of an SQL query can be cut down to a
   * subset of result rows or aggregated (like sum, max, min, avg, count).
   * The projection represents the select part of a query (select <<projection>> from ...)
   * 
   * By default criteria projects the result to all rows of a table. This can be changed by
   * using the rdbms.criteria::setProjection() method.
   *
   * exaple:
   * <?php
   *   // cut the result down to 2 atributes of a result set
   *   // for further examples with ProjectionList see rdbms.criterion.ProjectionList API doc
   *   // sql: select name, surname from person;
   *   Person::getPeer()->doSelect(create(new Criteria())->setProjection(
   *     Projections::projectionList()
   *     ->add(Projections::property(Person::column('name')))
   *     ->add(Projections::property(Person::column('surname')))
   *   ));
   *
   *   // just count a result
   *   // for further examples with ProjectionList see rdbms.criterion.CountProjection API doc
   *   // sql: select count(*) from person where ...
   *   Person::getPeer()->doSelect(create(new Criteria())->setProjection(Projections::count('*'))->add(...)...);
   *   
   *   // aggregated result set
   *   // sql: select avg(age) from person
   *   // sql: select min(age) from person
   *   // sql: select max(age) from person
   *   Person::getPeer()->doSelect(create(new Criteria())->setProjection(Projections::avg(Person::column('age'))));
   *   Person::getPeer()->doSelect(create(new Criteria())->setProjection(Projections::min(Person::column('age'))));
   *   Person::getPeer()->doSelect(create(new Criteria())->setProjection(Projections::max(Person::column('age'))));
   *   
   *   // every projection, except the ProjectionList, can be aliased in the second parameter
   *   // sql: select max(age) as `oldest` from person
   *   Person::getPeer()->doSelect(create(new Criteria())->setProjection(Projections::max(Person::column('age'), 'oldest')));
   * ?>
   *
   * @test     xp://net.xp_framework.unittest.rdbms.ProjectionTest
   * @see      xp://rdbms.Criteria
   * @see      xp://rdbms.criterion.ProjectionList
   * @see      xp://rdbms.criterion.CountProjection
   * @purpose  purpose
   */
  class Projections extends Object {

    /**
     * manufactor a new ProjectionList
     *
     * @param    properties
     * @return  rdbms.criterion.ProjectionList
     */
    public static function projectionList($properties= array()) {
      $pl= new ProjectionList();
      foreach ($properties as $property) $pl->add($property);
      return $pl;
    }
    
    /**
     * manufactor a new CountProjection
     *
     * @param   string fieldname optional default is *
     * @param   string alias optional
     * @return  rdbms.criterion.CountProjection
     */
    public static function count($field= '*', $alias= '') {
      return new CountProjection($field, $alias);
    }
    
    /**
     * manufactor a new PropertyProjection
     *
     * @param  string fieldname
     * @param  string alias optional
     * @return  rdbms.criterion.PropertyProjection
     */
    public static function property($field, $alias= '') {
      return new SimpleProjection($field, Projection::PROP, $alias);
    }
    
    /**
     * manufactor a new AverageProjection
     *
     * @param  string fieldname
     * @param  string alias optional
     * @return  rdbms.criterion.AverageProjection
     */
    public static function average($field, $alias= '') {
      return new SimpleProjection($field, Projection::AVG, $alias);
    }
    
    /**
     * manufactor a new SumProjection
     *
     * @param  string fieldname
     * @param  string alias optional
     * @return  rdbms.criterion.SumProjection
     */
    public static function sum($field, $alias= '') {
      return new SimpleProjection($field, Projection::SUM, $alias);
    }
    
    /**
     * manufactor a new MaxProjection
     *
     * @param  string fieldname
     * @param  string alias optional
     * @return  rdbms.criterion.MaxProjection
     */
    public static function max($field, $alias= '') {
      return new SimpleProjection($field, Projection::MAX, $alias);
    }
    
    /**
     * manufactor a new MinProjection
     *
     * @param  string fieldname
     * @param  string alias optional
     * @return  rdbms.criterion.MinProjection
     */
    public static function min($field, $alias= '') {
      return new SimpleProjection($field, Projection::MIN, $alias);
    }
    
  }
?>
