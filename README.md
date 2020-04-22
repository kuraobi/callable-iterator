# Callable Iterator

**⚠ WIP: this still needs a lot of work ⚠**

This is a very simple library that allows to iterate
over paginated results provided by a callable.

Basically, you need a callable which will receive an index as a parameter,
and return an iterable of results starting from this index.

When iterating over the CallableIterator,
your callable will be called and you will actually iterate over the results
returned by this callable.
Once you reach the end of the results, the callable will be called again
with the last position reached, prompting for results from this position.

This will continue as long as the callable does not return an empty iterable.

It is possible to set another callable to be called each time
the end of the current iterable is reached.

#### Example with Doctrine

```php
class ArticleRepository
{
    public function findFrom(int $minId, int $length): iterable
    {
        return $this->em->createQuery('SELECT a FROM Article a WHERE a.id >= :minId')
            ->setParameter('minId', $minId)
            ->setMaxResults($length)
            ->getResult();
    }
}

/** @var CallableIterator<Article> $articles */
$articles = new CallableIterator(fn (int $lastId): iterable => $this->articleRepository->findFrom($lastId + 1, 50));
// We clear the Entity Manager on each page to keep memory usage low 
$articles->setOnPageChange(function () {
    $this->entityManager->clear();
});

// Here we actually iterate over all articles
// Each time we reach the end of a result set (every 50 articles),
// The entity manager is cleared and the next result set is fetched
foreach ($articles as $article) {
    // Update the lastId fetched: this is the value that will be passed to the callable to load the next page
    $articles->setLastId($article->getId());
    // Do something with $article
    // ...
}
```
