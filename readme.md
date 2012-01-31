# Traveling Elephpant Problem - Remi's Solution

## Heads up!
    ** This code is written with one single purpose in mind: provide a winning solution for the Ibuildings TEP contest. It does NOT adhere to proper Design Patterns, Coding Standards, or anything else that makes code maintainable. DO NOT COPY/PASTE ANY OF THIS INTO YOUR OWN CODE AND ASSUME IT MUST BE THE BEST WAY BECAUSE I WROTE IT, OR BECAUSE THE CODE WAS FAST **


## The contest
You can read the whole results post about the [Ibuildings Code Challenge - Traveling Elephpant Problem](http://techportal.ibuildings.com/2010/05/17/the-elephpant-challenge-winners-and-results/), but let me give you a short recap: Given N locations, identified by their lat/long coordinates, find the single shortest route that visits each location exactly one time. This is known as the "traveling salesmen problem", an [NP-Complete problem](http://en.wikipedia.org/wiki/NP-complete), but because of the Elephpant involved, it was quickly dubbed the "Traveling Elephpant Problem". 

## Requirements
* The exact answer *must* be found. If the answer is only 1km off, the whole solution is worthless. This eliminates any form of approximation algorhitms.
* The solution *must* be in PHP. Sure, any lower level language will give much better results for brute-force calculations, but PHP is the requirement.
* The solution is judged on 3 items, all equally important: fastest calculation of correct answer, least amount of LOC, least amount of code complexity.

## Decisions

### Bruteforcing
Given that only the exact answer is a valid answer, I chose to ignore all form of approximation approaches. Approximation is much much faster, but far less accurate. Given the problem is NP-Complete, this means only bruteforcing the solution is a valid algorithm. Downside: bruteforcing is extremely slow (while each calculation may be extremely fast, you have to repeat it billions of times to find every possible combination, expressed as N! in math).

### Aborting early
My major gain in speed was by evaluating the "current" (incomplete) route with the shortest known route. If the "current" route-stub is already longer than the shortest known complete route, we can safely eliminate all routes that start with the same stub: the route will never get shorter. 

#### Improved Bidirectional Nearest Neigbor Alghoritm (IBNNA)
The aborting early would work even better if I had a very short path to start out with. For this, I implemented a variation of [NNA](http://en.wikipedia.org/wiki/Nearest_neighbour_algorithm). I split the route into 2 stubs: one from the start in the direction of the end, and one from the end in the direction of the start. Since each location has to be visited at least once, I then used the concept of NNA to see if the location should be stringed to the "start-stub" (the location is closest to the last location in the current start-stub) or to the "end-stub" (the location is closest to the first location in the current end-stub). 

It is a lot of extra code, but this finds a very short approximate solution, to abort even more stubs early. IBNNA is not accurate, but it is extremely fast. And the result is then used as the "known shortest" to start eliminating bruteforce stubs. This removes a *lot* of calculations in the bruteforce pattern, giving a *huge* gain in speed. On my Core2Duo (C2D), these two steps removed well over 75% of the solutions running time (from almost 10 minutes to under a minute).

### Pre-calculating distances
To get a further gain, I calculate the distance between all possible combinations of locations in advance. This way, the code has to do the lat/long calculations only once, and "cache" the results. All calculations then left are adding up distances. Note: the start and end location were given (for given and secret locations set), so they have been hardcoded and the "starts" are from the known start point to every normal location.

### Functional, recursive
There is not a single Object in the code, simply because those only aid the developer, not the code. Any code is faster (but *far* less maintainable) if you refactor OOP out of it. The fastest code is tail-recursive procedural, since PHP's stack is faster than anything I could build myself in userland. Also, writing my own stack, besides the major increase in LOC, would also imply I knew the number of locations in the secret file. Given the challenge clearly stated that it can be any number of locations (hence the N in the problem description), hardcoding it to 10 would reduce complexity, but also be a huge assumption, with the risk of a non-working solution should the secret file have 11 locations.

## Focus on Speed
As the challenge-roundup indicated, a lot of people focussed on speed. So did I. Mainly for 2 reasons:

1. Any speed difference would increase exponentially when the number of locations increased. My goal was to keep the solution under 10 minutes for lists of 12 locations, and I achieved that with the submitted solution. This goal was my personal choice, to give me confidence that my solution would come in in the top10 of fastest solutions, no matter how many locations the secret file had, or what CPU architecture it was ran on (which makes a *huge* difference!).
2. I was in contact with other people who participated in the same contest, and the speed differences was the only thing we could really compare. Everybody had debug lines in their code, so we couldn't really compare LOC, especially since we didn't know exactly how the LOC was counted. That comments were not counted was a given, but it was also given that concatenating lines would not be counted as a reduced LOC. So, likely, a phpcs transformation would be run over the code (kinda in a htmltidy way), which could turn any amount of LOC into any other amount of LOC. Not reliable to work on. The other judging point was complexity, something I couldn't get configured myself (likely for the same reason as Ibuildings had to change it), and more people had issues with it. So all we knew for sure was how long our solutions ran on our own CPU's. With that in mind, you start focussing on speed, since that's where you can improve on measurably.

    Sidenote: the Ibuildings' roundup post mentions my solution ran in ~11 seconds. This tells me they had lower grade hardware for the test machine (which was confirmed by Ibuildings) to run the solutions on. On my local C2D mac, it runs in a "real" time of 0m1.274s.


## "FAQ"
##### Under what license is this code released?
Given that this code is written with only one single purpose in mind, I don't think it's reusable in any way besides studying it. But I've been asked under what license it was, so I guess an educational work released under [CC-BY-NC-ND](http://creativecommons.org/licenses/by-nc-nd/3.0/) makes the most sense. If you need a version under any other license, feel free to reach out to me and explain why.

#### Is this really code you write?! It's unmaintainable and ugly!
This is nothing like I would write for any customer project, not even remotely close. This was written for fun, with the only single purpose of providing the fastest solution to an NP-Complete problem. If I had the choice between readability or saving 1ms per iteration, I went with the latter without a doubt. On the other hand, I may have made it too readable anyway, since even after 2 years, I can even recall my line of reasoning from looking at the code. My "customer" code definitely has proper phpDoc blocks, uses OOP, uses design patterns, etc etc. Then again, they usually also have a lot more LOC and a lot more readers, as well as a need to be maintained, so that's a very different situation

#### So, if you wrote it for fewest-loc, why do you have comments in there?
Comments were excluded from the LOC count. I did add a couple, on one hand, to keep my own sanity, on the other because I knew Ibuildings people would study the fastest solutions for curiosity reasons, and I didn't want to make their lives too hard. 

#### Cool code, but I found something that makes it even faster. Interested?
Not really. The contest ended on May 1st 2010, which makes any updates to it irrelevant now. So don't bother cloning this code, submitting issues or push requests for it, it's here for educational purposes only.

#### So, if the contest already ended, why did you publish this now? 
I always intended to publish my results, and I have even promised that to some contestants, but I was under the impression that I lost it. Recently, someone approached me about it, which somehow made me realize that the filename was "contest.php" while I had been looking for "tep.php" the whole time. This made me find it again, write up this "package insert" and publish it on github.

#### My app has a real need for a Traveling Salesman Problem solution. It's cool if I use your code, right?
**No:**

1. This code is unmaintainable, and should never ever appear in a project that needs to be maintained. Unless you love being tortured by the future maintainers and/or teammates, in which case… maybe you can convince them to do the torturing without introducing this code into the codebase? I loved writing this, but I would do very illegal things to whoever introduced this into a codebase I am responsible for. Really, don't. 
1. If you need an accurate result for an NP-Complete problem like this, you have to start bruteforcing. PHP is a high-level interpreted scripting language, which is the worst possible choice for such a solution. Go with C, or anything compiled and low-level like that, and you will get a huge gain in speed. Sure, the code takes longer to write, but the gain in production time is definitely worth it.
1. If you study the code, like the approach, and write your own code based on that knowledge, then that's all fine. I would really appreciate an attribution, but it would be your own work then. 

~RW